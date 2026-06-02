<?php

namespace App\Providers;

use App\Services\AccountContext;
use App\Services\LanguageService;
use App\Services\NotificationsService;
use App\Services\UserContextService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as DBQueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Livewire\Component;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // singletons (bind is default which is also singleton)
        $this->app->singleton(AccountContext::class);
        $this->app->singleton(LanguageService::class);
        $this->app->singleton(UserContextService::class);
        $this->app->singleton(NotificationsService::class);

        if ($this->app->isLocal()) {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }

        DBQueryBuilder::macro('search', function($attribute, $searchTerm){
            return $this->where($attribute, 'LIKE', "%{$searchTerm}%");
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Alternative option 1 (uncomment to use in multi-instance setup where each instance has APP_URL configured to load balancer and you want to restrict direct access to nodes)
        //URL::forceRootUrl(config('app.url'));

        // Alternative option 2 (uncomment to use in multi-instance setup when you want to keep having direct access to the same node unbothered if needed but to keep using load balancer in normal case)
        //$currentHost = request()->getHost();
        //$allowedHosts = array_map('trim', explode(',', env('ALLOWED_HOSTS', '')));
        //if (in_array($currentHost, $allowedHosts)) {
        //    URL::forceRootUrl(request()->getSchemeAndHttpHost());
        //} else {
        //    URL::forceRootUrl(config('app.url'));
        //}

        /** * Paginate a standard Laravel Collection. * * @param int $perPage * @param int $total * @param int $page * @param string $pageName * @return array */
        Collection::macro('paginate', function ($perPage, $total = null, $page = null, $pageName = 'page') {
            $page = $page ?: LengthAwarePaginator::resolveCurrentPage($pageName);
            return new LengthAwarePaginator($this->forPage($page, $perPage)->values(), $total ?: $this->count(), $perPage, $page, ['path' => LengthAwarePaginator::resolveCurrentPath(), 'pageName' => $pageName]);
        });

        Component::macro('notify', function ($type = '', $message = '') {
            $message = [trans($message),$type];
            $this->dispatchBrowserEvent('notify', $message);
        });

        // Component::macro('notify', function ($message) {
        //     $this->dispatchBrowserEvent('notify', $message);
        // });

        Blade::directive('onlydate', function ($expression) {
            return "<?php echo ($expression)->format('Y-m-d'); ?>";
        });

        Blade::directive('onlytime', function ($expression) {
            return "<?php echo ($expression)->format('H:i:s'); ?>";
        });

        Blade::componentNamespace('Asantibanez\\LaravelBladeSortable\\Views\\Components', 'laravel-blade-sortable');

        Builder::macro('whereLike', function ($attributes, string $searchTerm) {
            $this->where(function (Builder $query) use ($attributes, $searchTerm) {
                foreach (array_wrap($attributes) as $attribute) {
                    $query->when(
                        str_contains($attribute, '.'),
                        function (Builder $query) use ($attribute, $searchTerm) {
                            [$relationName, $relationAttribute] = explode('.', $attribute);

                            $query->orWhereHas($relationName, function (Builder $query) use ($relationAttribute, $searchTerm) {
                                $query->where($relationAttribute, 'LIKE', "%{$searchTerm}%");
                            });
                        },
                        function (Builder $query) use ($attribute, $searchTerm) {
                            $query->orWhere($attribute, 'LIKE', "%{$searchTerm}%");
                        }
                    );
                }
            });

            return $this;
        });
    }

}
