/**
 * Ayur Core - Admin Review Management Script
 * Handles WP Media Library picker for review photos
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        let frame;

        $('#ayurAddAdminPhotoBtn').on('click', function(e) {
            e.preventDefault();

            if (frame) {
                frame.open();
                return;
            }

            frame = wp.media({
                title: 'Select or Upload Review Photo',
                button: { text: 'Add to Review' },
                multiple: true,
                library: { type: 'image' }
            });

            frame.on('select', function() {
                const attachments = frame.state().get('selection').toJSON();
                const container = $('#ayurAdminPhotoList');

                attachments.forEach(function(att) {
                    const thumbUrl = (att.sizes && att.sizes.thumbnail) ? att.sizes.thumbnail.url : att.url;
                    const tile = $(`
                        <div class="ayur-admin-photo-tile" style="position:relative; width:80px; height:80px; border:1px solid #ccc; border-radius:4px; overflow:hidden;">
                            <img src="${thumbUrl}" style="width:100%; height:100%; object-fit:cover;">
                            <input type="hidden" name="review_photos[]" value="${att.id}">
                            <button type="button" class="ayur-remove-admin-photo" style="position:absolute; top:2px; right:2px; background:rgba(0,0,0,0.6); color:#fff; border:none; border-radius:50%; width:20px; height:20px; cursor:pointer;" onclick="this.parentElement.remove();">&times;</button>
                        </div>
                    `);
                    container.append(tile);
                });
            });

            frame.open();
        });
    });

})(jQuery);
