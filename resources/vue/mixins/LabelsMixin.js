export default {
    data() {
        return {
            selectedLabelsMap: {},
            fullySelectedGroupsMap: {},
            partiallySelectedGroupsMap: {},
            allLabelsGroups: [],
            editMode: {} // Track edit state per site
        };
    },

    methods: {
        itemKey(item) {
            item.id = item?.id ?? item?.dl_id ?? item?.dlg_id ?? null
            item.type = item?.type ?? (item?.dl_id && 'label') ?? (item?.dlg_id && 'group') ?? null

            return `${item.type}-${item.id}`
        },

        getAllLabelsForGroup(group) {
            let labels = [];
            const traverse = (node) => {
                if (node.labels && node.labels.length > 0) {
                    node.labels.forEach(traverse);
                } else {
                    labels.push(node);
                }
            };
            traverse(group);
            return labels;
        },

        findLabelGroup(label) {
            return this.allLabelsGroups.find(group =>
                this.getAllLabelsForGroup(group).some(l => l.dl_id === label.dl_id)
            );
        },

        initSiteLabels(siteId, labels) {
            this.$set(this.selectedLabelsMap, siteId, labels ? [...labels] : []);
            this.updateSelectedGroupsStatesForSite(siteId);
        },

        updateSelectedGroupsStatesForSite(siteId) {
            const selectedLabels = this.selectedLabelsMap[siteId];

            const fullySelectedGroups = this.allLabelsGroups.filter(group => {
                const groupLabels = this.getAllLabelsForGroup(group);
                const selectedLabelsInGroup = selectedLabels.filter(label =>
                    groupLabels.some(gl => gl.dl_id === label.dl_id)
                );
                return groupLabels.length > 0 && groupLabels.length === selectedLabelsInGroup.length;
            });

            const partiallySelectedGroups = this.allLabelsGroups.filter(group => {
                const groupLabels = this.getAllLabelsForGroup(group);
                const selectedLabelsInGroup = selectedLabels.filter(label =>
                    groupLabels.some(gl => gl.dl_id === label.dl_id)
                );
                return groupLabels.length > 0 &&
                    selectedLabelsInGroup.length > 0 &&
                    selectedLabelsInGroup.length < groupLabels.length;
            });

            this.$set(this.fullySelectedGroupsMap, siteId, fullySelectedGroups);
            this.$set(this.partiallySelectedGroupsMap, siteId, partiallySelectedGroups);
        },

        getDisplayLabelsForSite(siteId) {
            const selectedLabels = this.selectedLabelsMap[siteId];
            const fullySelectedGroups = this.fullySelectedGroupsMap[siteId];
            if (!selectedLabels || !fullySelectedGroups) return [];

            const items = [];

            fullySelectedGroups.forEach(group => {
                items.push({
                    type: 'group',
                    id: group.dlg_id,
                    name: group.dlg_name,
                    order: group.dlg_order
                });
            });

            selectedLabels.forEach(label => {
                const isInFullySelectedGroup = fullySelectedGroups.some(group =>
                    this.getAllLabelsForGroup(group).some(gl => gl.dl_id === label.dl_id)
                );

                if (!isInFullySelectedGroup) {
                    const parentGroup = this.findLabelGroup(label);
                    items.push({
                        type: 'label',
                        id: label.dl_id,
                        name: label.dl_name,
                        order: label.dl_order,
                        groupOrder: parentGroup ? parentGroup.dlg_order : Infinity
                    });
                }
            });

            return this.sortDisplayItems(items);
        },

        sortDisplayItems(items) {
            return items.sort((a, b) => {
                if (a.type === 'group' && b.type === 'group') {
                    return a.order - b.order;
                }
                if (a.type === 'label' && b.type === 'label') {
                    if (a.groupOrder !== b.groupOrder) {
                        return a.groupOrder - b.groupOrder;
                    }
                    return a.order - b.order;
                }
                const groupOrder = a.type === 'group' ? a.order : a.groupOrder;
                const otherGroupOrder = b.type === 'group' ? b.order : b.groupOrder;
                if (groupOrder !== otherGroupOrder) {
                    return groupOrder - otherGroupOrder;
                }
                return a.type === 'group' ? -1 : 1;
            });
        }
    }
};