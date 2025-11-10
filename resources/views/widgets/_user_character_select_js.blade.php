@php
    if (!isset($fieldPrefix)) {
        $fieldPrefix = '';
    }
@endphp
<script>
    $(document).ready(function() {
        var $userCharacters = $('#{{ $fieldPrefix }}userCharacters');
        var $userCharacterCategory = $('#{{ $fieldPrefix }}userCharacterCategory');
        $userCharacterCategory.on('change', function(e) {
            refreshCharacterCategory();
        });
        $('.{{ $fieldPrefix }}character-stack').on('click', function(e) {
            if (!$(this).parent().parent().hasClass('disabled')) {
                var $parent = $(this).parent().parent().parent();
                $parent.toggleClass('category-selected');
                $parent.find('.character-checkbox').prop('checked', $parent.hasClass('category-selected'));
                refreshCharacterCategory();
            }
        });
        $('.{{ $fieldPrefix }}characters-select-all').on('click', function(e) {
            e.preventDefault();
            console.log('select all');
            var $target = $('.{{ $fieldPrefix }}user-character:not(.hide):not(.select-disabled)');
            $target.addClass('category-selected');
            $target.find('.character-checkbox').prop('checked', true);
        });
        $('.{{ $fieldPrefix }}characters-clear-selection').on('click', function(e) {
            e.preventDefault();
            console.log('clear selection');
            var $target = $('.{{ $fieldPrefix }}user-character:not(.hide)');
            $target.removeClass('category-selected');
            $target.find('.character-checkbox').prop('checked', false);
        });

        function refreshCharacterCategory() {
            var display = $userCharacterCategory.val();
            $('.{{ $fieldPrefix }}user-character').addClass('hide');
            $userCharacters.find('.category-' + display).removeClass('hide');
        }
    });
</script>
