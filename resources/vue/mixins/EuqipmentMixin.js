export default {
    methods: {
        sharedFunction(data) {
            return data.map(item => ({ ...item, processed: true }));
        },
        filterActiveItems(data) {
            return data.filter(item => item.isActive);
        }
    }
};