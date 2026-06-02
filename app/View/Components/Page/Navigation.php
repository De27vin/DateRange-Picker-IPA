<?php

namespace App\View\Components\Page;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;

class Navigation extends Component
{
    public $locale;
    public $view;
    public $navigationState;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->local = session('locale','en');
        if(!Auth::user() || session('account.id') == null){
            ray('Navigation Guest');
            $this->view = 'page.navigation-guest';
        } else {
            ray('Navigation Admin');
            $this->view = 'page.navigation';
        }
    }

    /**
     * Get the view / contents that represents the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        // dd('hier');
        return view($this->view);
    }
}
