$(document).ready(function () {
    GroupForm.initialize();
    GroupUserControls.initialize();
});

var GroupForm = {

    $previewBtn: $('.group-new__preview-btn'),
    $descriptionInput: $('.group-new__description'),
    $descriptionPreview: $('.group-new__preview'),

    previewMode: false,

    initialize: function () {
        this.$previewBtn.on('click', function () {
            this.togglePreview();
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