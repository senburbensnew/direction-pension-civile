<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfilePhotoUpdateRequest;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\CivilStatus;
use App\Models\Gender;
use App\Services\Account\ProfileFieldCompletionService;
use App\Services\ImageStorageService;
use App\Support\Storage\UploadPaths;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user()->loadMissing([
            'gender',
            'civilStatus',
            'service',
            'institution',
        ]);

        $genders = Gender::query()->orderBy('name')->get();
        $civilStatuses = CivilStatus::query()->orderBy('name')->get();
        $profileCompletion = app(ProfileFieldCompletionService::class);

        return view('auth.profile', compact('user', 'genders', 'civilStatuses', 'profileCompletion'));
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = app(ProfileFieldCompletionService::class)->mergeCompletableFields(
            $user,
            $request->validated()
        );

        $user->fill($data);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Update the user's profile photo.
     */
    public function updateProfilePhoto(ProfilePhotoUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $file = $request->file('profile_photo');

        if (! $file || ! $file->isValid()) {
            $error = $file?->getError() ?? UPLOAD_ERR_NO_FILE;

            $message = match ($error) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Le fichier est trop volumineux pour le serveur.',
                UPLOAD_ERR_PARTIAL => 'Le téléversement a été interrompu. Réessayez.',
                UPLOAD_ERR_NO_FILE => 'Veuillez sélectionner une image.',
                UPLOAD_ERR_NO_TMP_DIR => 'Dossier temporaire introuvable sur le serveur.',
                UPLOAD_ERR_CANT_WRITE => 'Impossible d’écrire le fichier sur le serveur.',
                UPLOAD_ERR_EXTENSION => 'Une extension PHP a bloqué le téléversement.',
                default => 'Le téléversement a échoué. Réessayez avec un JPEG ou PNG (max. 5 Mo).',
            };

            return Redirect::route('profile.edit')->withErrors([
                'profile_photo' => $message,
            ]);
        }

        Storage::disk(UploadPaths::publicDisk())->makeDirectory(UploadPaths::userProfilePhoto());

        if ($user->profile_photo) {
            app(ImageStorageService::class)->deleteWithVariants($user->profile_photo);
        }

        $path = app(ImageStorageService::class)->storeUploadedImage(
            $file,
            UploadPaths::userProfilePhoto(),
            config('media.upload_max_widths.profile'),
        );

        $user->update([
            'profile_photo' => $path,
        ]);

        return Redirect::route('profile.edit')->with('status', 'profile-photo-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::guard('web')->logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
