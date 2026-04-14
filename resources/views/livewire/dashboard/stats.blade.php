<div wire:poll.visible.30s="updateDashboardStats" class="bottom-underline py-4 dashboard-summary">
    <div class="dashboard-summary__grid">
        @foreach($stats as $statitem)
            <div class="dashboard-summary__card" style="background-color: {{ $statitem['color'] }}; color: {{ $statitem['text-color'] }};">
                <div class="dashboard-summary__top">
                    <div class="dashboard-summary__title">{{ $statitem['label'] }}</div>

                    <div class="dashboard-summary__preview">
                        <svg
                            class="dashboard-summary__sparkline"
                            viewBox="0 0 {{ $statitem['preview']['width'] }} {{ $statitem['preview']['height'] }}"
                            preserveAspectRatio="none"
                            aria-hidden="true"
                        >
                            <line
                                x1="0"
                                y1="{{ $statitem['preview']['height'] }}"
                                x2="{{ $statitem['preview']['width'] }}"
                                y2="{{ $statitem['preview']['height'] }}"
                                class="dashboard-summary__baseline"
                            />

                            @foreach($statitem['preview']['series'] as $series)
                                @if($series['points'] !== '')
                                    <polyline
                                        points="{{ $series['points'] }}"
                                        fill="none"
                                        stroke="{{ $series['color'] }}"
                                        stroke-width="2.4"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                    <circle
                                        cx="{{ $series['lastPoint']['x'] }}"
                                        cy="{{ $series['lastPoint']['y'] }}"
                                        r="2.2"
                                        fill="{{ $series['color'] }}"
                                    />
                                @endif
                            @endforeach
                        </svg>

                        <div class="dashboard-summary__dates">
                            @foreach($statitem['preview']['labels'] as $label)
                                <span>{{ $label }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="dashboard-summary__bottom">
                    <div class="dashboard-summary__labels">
                        @foreach($statitem['values'] as $row)
                            <div class="dashboard-summary__metric-label">{{ $row['label'] }}</div>
                        @endforeach
                    </div>

                    <div class="dashboard-summary__values">
                        @foreach($statitem['values'] as $row)
                            <div class="dashboard-summary__metric-value">{{ $row['value'] }}</div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<style>
    .dashboard-summary__grid {
        display: grid;
        gap: 1rem;
        grid-template-columns: repeat(1, minmax(0, 1fr));
    }

    .dashboard-summary__card {
        min-height: 11.25rem;
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 0.85rem;
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.06);
        padding: 1rem 1rem 0.9rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        overflow: hidden;
    }

    .dashboard-summary__top {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 9.5rem;
        gap: 1rem;
        align-items: start;
    }

    .dashboard-summary__title {
        font-size: 1.2rem;
        font-weight: 600;
        line-height: 1.15;
        padding-top: 0.15rem;
    }

    .dashboard-summary__preview {
        display: flex;
        flex-direction: column;
        align-items: stretch;
        gap: 0.35rem;
        margin-left: auto;
        width: 100%;
    }

    .dashboard-summary__sparkline {
        width: 100%;
        height: 3.9rem;
        overflow: visible;
    }

    .dashboard-summary__baseline {
        stroke: rgba(15, 23, 42, 0.14);
        stroke-width: 1;
    }

    .dashboard-summary__dates {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.35rem;
        font-size: 0.68rem;
        line-height: 1;
        opacity: 0.72;
        text-align: center;
        letter-spacing: 0.01em;
    }

    .dashboard-summary__bottom {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 0.75rem;
        align-items: end;
        margin-top: 0.9rem;
    }

    .dashboard-summary__labels,
    .dashboard-summary__values {
        display: grid;
        gap: 0.38rem;
        font-size: 0.98rem;
        line-height: 1.2;
    }

    .dashboard-summary__labels {
        min-width: 0;
    }

    .dashboard-summary__metric-label {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .dashboard-summary__values {
        text-align: right;
        font-weight: 600;
    }

    @media (min-width: 640px) {
        .dashboard-summary__grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (min-width: 1024px) {
        .dashboard-summary__grid {
            grid-template-columns: repeat(5, minmax(0, 1fr));
        }

        .dashboard-summary__card {
            min-height: 12rem;
        }

        .dashboard-summary__top {
            grid-template-columns: minmax(0, 1fr) 8.6rem;
            gap: 0.8rem;
        }
    }

    @media (max-width: 479px) {
        .dashboard-summary__top {
            grid-template-columns: minmax(0, 1fr);
        }

        .dashboard-summary__preview {
            margin-left: 0;
            max-width: none;
        }
    }
</style>
