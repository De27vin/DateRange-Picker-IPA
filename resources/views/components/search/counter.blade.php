@props(['icon' => 'building'])

<div x-data="counterComponent()" x-init="initialize()">
    <div x-show="loading" class="flex items-center uppercase text-sm" style="font-weight: bold;">
        <!-- Single Pulsating Circle -->
        <i class="f7-icons text-lg ml-4">{{ $icon }}</i>
        <div class="v-spinner-custom" style="padding-left: 0.5rem;">
            <div class="v-pulse-custom" x-bind:style="spinnerStyle"></div>
        </div>
    </div>
    <div x-show="!loading" class="flex items-center uppercase text-sm" style="font-weight: bold;">
{{--        <i class="f7-icons text-lg ml-4">{{ $icon }}</i> <span class="" x-text="total" style="padding-left: 0.5rem;"></span>--}}

        <span class="tt cursor-default">
            <div class="tts"><i class="f7-icons text-lg ml-4">{{ $icon }}</i> <span class="" x-text="total" style="padding-left: 0.5rem;"></span></div>
            <span class="ttt elip ttt-r bg-white border border-slate-300 text-dark shadow-md text-sm" style="font-weight: normal;">{{ __('Results count') }}</span>
        </span>
    </div>
</div>

<script>
    function counterComponent() {
        return {
            total: null,
            loading: true,
            spinnerStyle: {
                backgroundColor: 'lightblue',
                width: '10px', // Larger size for the single circle
                height: '10px',
                margin: 'auto',
                borderRadius: '100%',
                display: 'inline-block',
                animationName: 'v-pulseStretch-custom',
                animationDuration: '1s',
                animationIterationCount: 'infinite',
                animationTimingFunction: 'ease-in-out',
                animationFillMode: 'both'
            },
            initialize() {
                if (window.latestTotalCount !== null) {
                    this.total = window.latestTotalCount;
                    this.loading = false;
                }

                window.addEventListener('total_count_load', () => {
                    this.loading = true;
                });

                window.addEventListener('total_count_updated', (event) => {
                    this.total = event.detail.total;
                    this.loading = false;
                });
            }
        }
    }
</script>

<style>
.v-spinner-custom {
    display: flex;
    justify-content: center;
    align-items: center;
    margin: 10px auto;
    height: 10px; /* Add a height to ensure it's visible */
}

.v-pulse-custom {
    background-color: lightblue;
    width: 10px;
    height: 10px;
    border-radius: 100%;
    display: inline-block;
    animation: v-pulseStretch-custom 1s infinite ease-in-out;
    -webkit-animation: v-pulseStretch-custom 1s infinite ease-in-out;
}

@keyframes v-pulseStretch-custom {
    0%, 100% {
        transform: scale(1);
        opacity: 1;
    }
    50% {
        transform: scale(0.5);
        opacity: 0.5;
    }
}
</style>
