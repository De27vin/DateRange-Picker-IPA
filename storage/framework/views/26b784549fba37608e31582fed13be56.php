<script>
    const laravelBladeSortable = () => {
        return {
            name: '',
            sortOrder: [],
            animation: 150,
            ghostClass: '',
            dragHandle: null,
            group: null,
            allowSort: true,
            allowDrop: true,

            wireComponent: null,
            wireOnSortOrderChange: null,

            init() {
                this.sortOrder = this.computeSortOrderFromChildren()

                window.Sortable.create(this.$refs.root, {
                    handle: this.dragHandle,
                    animation: this.animation,
                    ghostClass: this.ghostClass,
                    group: {
                        name: this.group,
                        put: this.allowDrop,
                    },
                    sort: this.allowSort,
                    onSort: evt => {
                        const previousSortOrder = [...this.sortOrder]
                        this.sortOrder = this.computeSortOrderFromChildren()

                        if (!this.wireComponent) {
                            return
                        }

                        const from = evt?.from?.dataset?.name
                        const to = evt?.to?.dataset?.name

                        this.wireComponent.call(
                            this.wireOnSortOrderChange,
                            this.sortOrder,
                            previousSortOrder,
                            this.name,
                            from,
                            to,
                        )
                    },
                });
            },

            computeSortOrderFromChildren() {
                return [].slice.call(this.$refs.root.children)
                    .map(child => child.dataset.sortKey)
                    .filter(sortKey => sortKey)
            }
        }
    }
</script>
<?php /**PATH C:\Users\devin\Documents\SynologyDrive\Privat\Serv24\Laravel-VueJS\Kopie-UCP-web-UI\src\vendor\jacekdziurdzikowski\laravel-blade-sortable\src/../resources/views/components/scripts.blade.php ENDPATH**/ ?>