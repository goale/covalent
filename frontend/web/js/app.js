$(document).ready(function () {
    GroupForm.initialize();
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