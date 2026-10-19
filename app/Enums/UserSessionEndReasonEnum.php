<?php

namespace App\Enums;

enum UserSessionEndReasonEnum: string
{
    case VoluntaryLogout = 'voluntary_logout';
    case AdminRevokeOne = 'admin_revoke_one';
    case AdminRevokeAll = 'admin_revoke_all';
    case PasswordReset = 'password_reset';

    public function label(): string
    {
        return match ($this) {
            self::VoluntaryLogout => __('messages.admin_sessions.end_reason_voluntary'),
            self::AdminRevokeOne => __('messages.admin_sessions.end_reason_admin_one'),
            self::AdminRevokeAll => __('messages.admin_sessions.end_reason_admin_all'),
            self::PasswordReset => __('messages.admin_sessions.end_reason_password_reset'),
        };
    }
}
