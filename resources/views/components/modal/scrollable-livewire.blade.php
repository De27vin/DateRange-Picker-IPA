
@props([
    'header' => '',
    'isModalOpenProp' => null
])



<main
        class="mx-auto max-w-4xl bg-white h-screen"
        x-data="{ isModalOpen: @entangle($isModalOpenProp), referencingId: null}"
        @keydown.escape.window="isModalOpen = false"
        @open-livewire-modal.window="isModalOpen = true;"
>

    <section class="flex flex-wrap p-4">

        <!-- overlay -->
        <div
                class="overflow-auto"
                style="background-color: rgba(0,0,0,0.2); z-index: 200;"
                x-show="isModalOpen"
                :class="{ 'fixed inset-0 flex items-start justify-center': isModalOpen }"
        >
            <!-- dialog -->
            <div
                    class="bg-white shadow-2xl m-4 sm:m-8"
                    x-show="isModalOpen"
                    @click.away="isModalOpen = false"
            >
                <div class="flex justify-between items-center border-b p-2 text-xl">
                    <h6 class="text-xl font-bold ml-5">{{ $header }}</h6>
                    <button type="button" @click="isModalOpen = false">✖</button>
                </div>
                <div class="p-2">

                    {{ $slot }}

                </div>

            </div><!-- /dialog -->

        </div><!-- /overlay -->

    </section>
</main>