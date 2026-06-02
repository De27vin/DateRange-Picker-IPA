<?php
namespace App\Http\Livewire\Auth;

use App\Services\UserContextService;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Login extends Component
{
    private UserContextService $userContext;

    public function __construct($id = null)
    {
        parent::__construct($id);
        $this->userContext = app(UserContextService::class);
    }

    public $users, $email, $password, $name;
    public $remember_me = true;
    public $accountForm = false;
    public $hasUpdate;

    public function render()
    {
        return view('livewire.auth.login');
    }

    private function resetInputFields(){
        $this->name = '';
        $this->email = '';
        $this->password = '';
    }
 
    public function login()
    {

        $validatedDate = $this->validate([
            'email' => 'required|email',
            'password' => 'required',
            'remember_me' => 'nullable',
        ], [
            'email.required' => __('Email is required'),
            'email.email' => __('Please enter a valid email'),
            'password.required' => __('Password is required'),
        ]);

        $rememberMe = (isset($this->remember_me));
        if (Auth::attempt(array('email' => $this->email, 'password' => $this->password),$rememberMe)) {
            $user = Auth::user();

            // TODO: temporary solution for liftcare subtenants - remove after implementing granular permissions system
            if ($user->isSubtenantUser()) {
                Auth::logout();
                session()->flash('error', __('Access to web application is restricted. Please use the mobile application.'));
                return;
            }

            $accounts = $this->userContext->getUserAccounts();
            // session(['locale'       => Auth::user()->locale->language->language_code, 'en']);

            $user->updateLoginStats($_SERVER['REMOTE_ADDR']);

            if(Auth::user()->hasRole('site')) {
                return redirect()->intended('accounts');
            } elseif($accounts->count() > 1){
                return redirect()->intended('accounts');
            } else {
                // user is member of only one account. Therefore redirect to dashboard
                $account = $accounts->first();
                $this->userContext->switchAccount($account->account_id);
                return redirect()->intended('dashboard');
            }
        } else {
            session()->flash('error', __('Incorrect email or password.'));
        }
    }

 }
