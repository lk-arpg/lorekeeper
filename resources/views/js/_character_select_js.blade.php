<script>
    $(document).ready(function() {
        var $addCharacter = $('#addCharacter');
        var $components = $('#characterComponents');
        var $rewards = $('#rewards');
        var $characters = $('#characters');
        var count = 0;

        $('#characters .submission-character').each(function(index) {
            attachListeners($(this));
        });

        $addCharacter.on('click', function(e) {
            e.preventDefault();
            $clone = $components.find('.submission-character').clone();
            attachListeners($clone);
            attachRewardTypeListener($clone.find('.character-rewardable-type'));
            $characters.append($clone);
            $clone.find('.character-code').selectize();
            $clone.find('.criterion-character-section').removeClass('hide');
            count++;
        });

        function attachListeners(node) {
            node.find('.character-code').on('change', function(e) {
                var $parent = $(this).parent().parent().parent().parent();
                $parent.find('.character-image-loaded').load('{{ url('submissions/new/character') }}/' + $(this).val(), function(response, status, xhr) {
                    $parent.find('.character-image-blank').addClass('hide');
                    $parent.find('.character-image-loaded').removeClass('hide');
                    $parent.find('.character-rewards').removeClass('hide');
                    updateRewardNames(node, node.find('.character-info').data('id'));
                });
            });
            node.find('.remove-character').on('click', function(e) {
                e.preventDefault();
                $(this).parent().parent().parent().remove();
            });
            node.find('.remove-reward').on('click', function(e) {
                e.preventDefault();
                $(this).parent().parent().remove();
            });
            node.find('.add-reward').on('click', function(e) {
                e.preventDefault();
                $clone = $components.find('.character-reward-row').clone();
                $clone.find('.remove-reward').on('click', function(e) {
                    e.preventDefault();
                    $(this).parent().parent().remove();
                });
                updateRewardNames($clone, node.find('.character-info').data('id'));
                $(this).parent().parent().find('.character-rewards').append($clone);
                attachRewardTypeListener(node.find('.character-rewardable-type'));
            });
            attachRewardTypeListener(node.find('.character-rewardable-type'));

            //criterias
            node.find('.add-character-calc').on('click', function(e) {
                e.preventDefault();
                var clone = $('#copy-character-calc').clone();
                clone.removeClass('hide');
                var input = clone.find('[name*=criterion]');
                var count = $('.criterion-select').length;
                input.attr('name', input.attr('name').replace('slug', node.find('.character-code').val()));
                input.attr('name', input.attr('name').replace('#', count))
                clone.find('.criterion-select').on('change', loadForm);
                clone.find('.delete-calc').on('click', deleteCriterion);
                clone.removeAttr('id');
                $(this).parent().parent().append(clone);
            });
        }

        function attachRewardTypeListener(node) {
            node.on('change', function(e) {
                var val = $(this).val();
                var $cell = $(this).parent().parent().find('.lootDivs');

                $cell.children().addClass('hide');
                $cell.children().children().val(null);

                if (val == 'Item') {
                    $cell.children('.character-items').addClass('show');
                    $cell.children('.character-items').removeClass('hide');
                    $cell.children('.character-items');
                } else if (val == 'Currency') {
                    $cell.children('.character-currencies').addClass('show');
                    $cell.children('.character-currencies').removeClass('hide');
                } else if (val == 'LootTable') {
                    $cell.children('.character-tables').addClass('show');
                    $cell.children('.character-tables').addClass('show');
                    $cell.children('.character-tables').removeClass('hide');
                }
            });
        }

        function updateRewardNames(node, id) {
            node.find('.character-rewardable-type').attr('name', 'character_rewardable_type[' + id + '][]');
            node.find('.character-rewardable-quantity').attr('name', 'character_rewardable_quantity[' + id + '][]');
            node.find('.character-currency-id').attr('name', 'character_rewardable_id[' + id + '][]');
            node.find('.character-item-id').attr('name', 'character_rewardable_id[' + id + '][]');
            node.find('.character-table-id').attr('name', 'character_rewardable_id[' + id + '][]');
        }

        // start criteria
        function loadForm(e) {
            var id = $(this).val();
            var promptId = $('#prompt').val();
            var formId = $(this).attr('name').split('[')[2].replace(']', '');

            if (id) {
                var form = $(this).closest('.card').find('.form');
                form.load("{{ url('criteria/character/') }}/" + $(this).closest('.submission-character').find('.character-code').val() + "/prompt/" + id + "/" + promptId + "/" + formId, (response, status, xhr) => {
                    if (status == "error") {
                        var msg = "Error: ";
                        console.error(msg + xhr.status + " " + xhr.statusText);
                    } else {
                        form.find('[data-toggle=tooltip]').tooltip({
                            html: true
                        });
                        form.find('[data-toggle=toggle]').bootstrapToggle();
                    }
                });
            }
        }

        function deleteCriterion(e) {
            e.preventDefault();
            var toDelete = $(this).closest('.card');
            toDelete.remove();
        }
        //end criteria
    });
</script>
