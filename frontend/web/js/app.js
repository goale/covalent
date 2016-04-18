$(document).ready(function () {
    // Select text on focus
    $('.select-on-focus').on('focusin', function () {
        return $(this).select().one('mouseup', function (e) {
            return e.preventDefault()
        });
    });

    TabControls.initialize();
    GroupForm.initialize();
    GroupUserControls.initialize();
});

var TabControls = {
    $tabControls: $('.group-detail__tabs'),

    initialize: function () {
        var self = this;

        this.$tabControls.on('click', 'li', function (e) {
            e.preventDefault();
            var $tab = $(this);

            if ($tab.hasClass('active')) {
                return false;
            }

            $tab
                .addClass('active')
                .siblings().removeClass('active');

            self.handleTabContainers($tab);
        });
    },

    handleTabContainers: function (tab) {
        var containerId = tab.data('tab');
        $('#' + containerId).show().siblings().hide();
    }
};

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
        this.EDIT_URL = this.$groupForm.data('url');
        this.DELETE_URL = this.$deleteGroupBtn.data('url');
        this.BACK_URL = this.$deleteGroupBtn.data('back-url');

        this.$previewBtn.on('click', function () {
            this.togglePreview();
        }.bind(this));

        this.$changeOwnerSelect.on('change', function () {
            this.$changeOwnerBtn.prop('disabled', false);
        }.bind(this));

        this.$changeOwnerBtn.on('click', function () {
            var newOwner = this.$changeOwnerSelect.val(),
                username = this.$changeOwnerSelect.children(':selected').text().trim();

            if (prompt('Type selected username to confirm') === username) {
                this.changeGroupOwner(newOwner);
            }
        }.bind(this));

        this.$deleteGroupBtn.on('click', function () {
            var name = this.$groupForm.data('name');

            if (prompt('Type group name to confirm deletion') === name) {
                this.deleteGroup();
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

    changeGroupOwner: function (user) {
        var self = this;
        $.ajax({
            url: this.EDIT_URL,
            data: 'type=owner&user=' + user,
            type: 'PATCH',
            dataType: 'json',
            success: function (data) {
                if (data.hasOwnProperty('needRedirect') && data.needRedirect) {
                    window.location = self.BACK_URL;
                }
            }
        });
    },
    deleteGroup: function () {
        var self = this;
        $.ajax({
            url: this.DELETE_URL,
            type: 'DELETE',
            dataType: 'json',
            success: function (data) {
                if (data.success) {
                    window.location = self.BACK_URL;
                }
            }
        });
    }
};

var GroupUserControls = {

    $groupAddUserForm: $('form[name="group-user-add"]'),
    $usersAddForm: $('.group-detail__users-add'),

    usersContainer: '.group-detail__users',
    userItem: '.group-detail__item',
    removeBtn: '.group-detail__users-leave',
    roleSelect: '.group-detail__users-role',
    userAddBtn: '.group-detail__users-add-btn',
    errorBox: '.group-detail__users-add-error',

    initialize: function () {
        var self = this;

        this.URL = this.$usersAddForm.data('url');

        this.$usersAddForm.on('change', '.group-detail__users-add-user', function () {
            if ($(this).val().length > 0) {
                $(self.userAddBtn).prop('disabled', false);
            } else {
                $(self.userAddBtn).prop('disabled', true);
            }
        });

        this.$groupAddUserForm.submit(function () {
            self.addMember($(this).serialize());
            return false;
        });

        $(this.usersContainer).on('click', this.removeBtn, function () {
            var $item = $(this).closest(self.userItem);
            self.deleteMember($item.data('user'), function () {
                $item.remove();
            });
        });

        $(this.usersContainer).on('change', this.roleSelect, function () {
            var $item = $(this).closest(self.userItem);
            self.changeMemberRole($item.data('user'), $(this).val());
        });
    },

    addMember: function (data) {
        $(this.errorBox).text('');
        var self = this;

        $.ajax({
            url: this.URL,
            type: 'POST',
            data: data,
            dataType: 'html',
            success: function (data) {
                self.$usersAddForm.before(data);
                self.$groupAddUserForm[0].reset();
            },
            error: function (xhr) {
                var errorText = xhr.responseText.split(': ')[1];
                $(self.errorBox).text(errorText);
            }
        });
    },

    deleteMember: function (user, callback) {
        $.ajax({
            url: this.URL,
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

    changeMemberRole: function (user, role) {
        $.ajax({
            url: this.URL,
            type: 'PATCH',
            data: 'user=' + user + '&role=' + role,
            dataType: 'json'
        });
    }
};