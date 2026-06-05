<?php

namespace App\View\Components;

use App\Models\Official;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Presentation extends Component
{
    public string $role;
    public string $nom;
    public string $sexe;
    public string $lienProfil;
    public string $lienDiscours;
    public string $mobileImage;
    public string $desktopImage;
    public bool $showProfileLink;
    public bool $showSpeechLink;
    public ?Official $official;

    public function __construct(
        string  $slug          = '',
        string  $role          = '',
        string  $nom           = '',
        string  $sexe          = 'M',
        string  $lienProfil    = '',
        string  $lienDiscours  = '',
        string  $mobileImage   = '',
        string  $desktopImage  = '',
        bool    $showProfileLink = false,
        bool    $showSpeechLink  = false,
    ) {
        $this->official = $slug ? Official::findBySlug($slug) : null;

        if ($this->official) {
            $this->role           = $this->official->role;
            $this->nom            = $this->official->nom;
            $this->sexe           = $this->official->sexe;
            $this->desktopImage   = $this->official->photoUrl();
            $this->mobileImage    = $this->official->photoUrl();
            $this->lienProfil     = $lienProfil  ?: route('quisommesnous.profil', ['role' => $this->official->slug]);
            $this->lienDiscours   = $lienDiscours ?: route('quisommesnous.mots',  ['role' => $this->official->slug]);
            $this->showProfileLink = $this->official->hasBiographie();
            $this->showSpeechLink  = $this->official->hasDiscours();
        } else {
            $this->role           = $role;
            $this->nom            = $nom;
            $this->sexe           = $sexe;
            $this->lienProfil     = $lienProfil;
            $this->lienDiscours   = $lienDiscours;
            $this->mobileImage    = $mobileImage;
            $this->desktopImage   = $desktopImage;
            $this->showProfileLink = $showProfileLink;
            $this->showSpeechLink  = $showSpeechLink;
        }
    }

    public function render(): View|Closure|string
    {
        return view('components.presentation');
    }
}
