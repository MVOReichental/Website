Permissions2jsTree = {
    Group: function(group, checkedGroups) {
        this.id = group.id;
        this.text = group.title;

        if (checkedGroups.indexOf(this.id) !== -1) {
            this.state = {
                selected: true
            }
        }

        this.children = Permissions2jsTree.convertGroupList(group.subGroups, checkedGroups);
    },

    convertGroupList: function(groupList, checkedGroups) {
        var groups = [];

        for (var index = 0; index < groupList.length; index++) {
            groups.push(new Permissions2jsTree.Group(groupList[index], checkedGroups));
        }

        return groups;
    }
};