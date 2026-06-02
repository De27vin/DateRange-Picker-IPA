{{--
    Export progress bar.

    Reads Alpine state from the parent exportHandler() scope:
      polling       — bool, shows/hides this element
      progress      — 0-100
      progressLabel — string set via exportHandler config

    Positioned absolute right-0 mt-2 — directly below the trigger button,
    same spot as the format dropdown and the email-sent toast.
--}}
<div x-show="polling" x-cloak>
    <div class="absolute right-0 mt-2 w-72 bg-white rounded-xl shadow-lg border border-gray-200 p-4 z-50">
        <div class="space-y-2">
            <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-700" x-text="progressLabel"></span>
                <template x-if="progress >= 100">
                    <span class="text-green-500">✓</span>
                </template>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2.5">
                <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-300"
                     :style="`width: ${progress}%`">
                </div>
            </div>
            <span class="text-xs text-gray-500" x-text="`${progress}% complete`"></span>
        </div>
    </div>
</div>
