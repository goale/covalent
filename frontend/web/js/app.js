$(document).ready(function () {
    GroupForm.initialize();
    GroupUserControls.initialize();
});

var GroupForm = {

    $groupForm: $('.group-form'),
    $previewBtn: $('.group-form__preview-btn'),
    $descriptionInput: $('.group-form__description'),
    $descriptionPreview: $('.group-form__preview'),
    $changeOwnerSelect: $('.group-form__owner-change'),
    $changeOwnerBtn: $('.group-form__owner-change-btn'),
    $deleteGroupBtn: $('.group-form__delete-btn'),

    previewMode: false,

    initialize: function () {
        this.$previewBtn.on('click', function () {
            this.togglePreview();
        }.bind(this));

        this.$changeOwnerSelect.on('change', function () {
            this.$changeOwnerBtn.prop('disabled', false);
        }.bind(this));

        this.$changeOwnerBtn.on('click', function () {
            var newOwner = this.$changeOwnerSelect.val(),
                groupId = this.$groupForm.data('group'),
                username = this.$changeOwnerSelect.children(':selected').text().trim();

            if (prompt('Type selected username to confirm') === username) {
                this.changeGroupOwner(groupId, newOwner);
            }
        }.bind(this));

        this.$deleteGroupBtn.on('click', function () {
            var groupId = this.$groupForm.data('group'),
                name = this.$groupForm.data('name');

            if (prompt('Type group name to confirm deletion') === name) {
                this.deleteGroup(groupId);
            }
        }.bind(this));
    },

    togglePreview: function () {
        if (this.previewMode) {
            this.$descriptionPreview.hide();
            this.$descriptionInput.show();
        } else {
            this.$descriptionPreview.html(marked(this.$descriptionInput.val()));
            this.$descriptionInput.hide();
            this.$descriptionPreview.show();
        }
        
        this.$previewBtn.toggleClass('preview-btn-active');

        this.previewMode = !this.previewMode;
    },

    changeGroupOwner: function (group, user) {
        $.ajax({
            url: '/groups/' + group + '/edit',
            data: 'type=owner&user=' + user,
            type: 'PATCH',
            dataType: 'json',
            success: function (data) {
                if (data.needRedirect) {
                    window.location = '/groups';
                }
            }
        });
    },
    deleteGroup: function (group) {
        $.ajax({
            url: '/groups/' + group + '/delete',
            type: 'DELETE',
            dataType: 'json',
            success: function (data) {
                if (data.success) {
                    window.location = '/groups';
                }
            }
        });
    }
};

var GroupUserControls = {

    $groupAddUserForm: $('form[name="group_user_add"]'),
    $usersAddForm: $('.group-detail__users-add'),

    usersContainer: '.group-detail__users',
    userItem: '.group-detail__users-item',
    removeBtn: '.group-detail__users-leave',
    roleSelect: '.group-detail__users-role',

    initialize: function () {
        var self = this;

        this.$groupAddUserForm.submit(function () {
            self.addUserToGroup($(this).serialize());
            return false;
        });

        $(this.usersContainer).on('click', this.removeBtn, function () {
            var $item = $(this).closest(self.userItem);
            self.removeUserFromGroup($item.data('user'), $item.data('group'), function () {
                $item.remove();
            });
        });

        $(this.usersContainer).on('change', this.roleSelect, function () {
            var $item = $(this).closest(self.userItem);
            self.changeUserRole(self.$groupAddUserForm.data('group'), $item.data('user'), $(this).val());
        });
    },

    addUserToGroup: function (data) {
        var self = this;

        $.ajax({
            url: '/groups/' + this.$groupAddUserForm.data('group') + '/users',
            type: 'POST',
            data: data,
            dataType: 'html',
            success: function (data) {
                self.$usersAddForm.before(data);
            }
        });
    },

    removeUserFromGroup: function (user, group, callback) {
        $.ajax({
            url: '/groups/' + group + '/users',
            type: 'DELETE',
            data: 'user=' + user,
            dataType: 'json',
            success: function (data) {
                if (data.success) {
                    callback();
                }
            }
        });
    },

    changeUserRole: function (group, user, role) {
        $.ajax({
            url: '/groups/' + group + '/users',
            type: 'PATCH',
            data: 'user=' + user + '&role=' + role,
            dataType: 'json'
        });
    }
};