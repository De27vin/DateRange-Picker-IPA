<?php
namespace App\Http\Livewire\User;

use App\Rules\PasswordStrengthRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Livewire\Component;

class ChangePassword extends Component
{

    public $oldPassword;
    public $newPassword;
    public $newPasswordConfirm;

    public function mount()
    {
        if (!session('account.id')) {
            \Log::error('session account id not found in ChangePassword component');
            abort(500);
        }
    }

    public function rules()
    {
        $accountId = session('account.id');
        return [
            'oldPassword' => ['required'],
            'newPassword' => ['required', 'same:newPassword', new PasswordStrengthRule($accountId)],
            'newPasswordConfirm' => ['required', 'same:newPassword', new PasswordStrengthRule($accountId)],
        ];
    }

    public function messages()
    {
        return [
            'oldPassword.required' => __('Current password is required'),

            'newPassword.required' => __('New password is required'),
            'newPassword.same' => __('Password is not the same as confirmed password'),

            'newPasswordConfirm.required' => __('New password confirmation is required'),
            'newPasswordConfirm.same' => __('Password is not the same as confirmed password'),
        ];
    }

    public function render()
    {
        return view('livewire.user.change-password');
    }

    public function changePassword()
    {
        $this->validate();

        $user = Auth::user();
        if (!Hash::check($this->oldPassword, $user->user_password)) {
            $this->addError('oldPassword', __('Current password is invalid'));
            return;
        }

        $user->user_password = bcrypt($this->newPassword);
        $user->save();

        $this->notify('success', __('Password changed'));
        return Redirect::to('/user-profile');
    }
}