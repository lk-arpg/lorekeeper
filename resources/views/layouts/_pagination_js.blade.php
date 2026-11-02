<script>
    $('.pageSelectPopover').popover()

    function onPaginationClick(e) {
        const pageCurrent = new URL(window.location.href);
        pageCurrent.searchParams.set("page", e.currentTarget.parentElement.querySelector('.paginationPageRange').value);
        document.location.href = pageCurrent.href;
    }

    $('.pageSelectPopover').on('shown.bs.popover', function() {
        var $paginationPopoverClone = $('.pagination-popover-origin').clone();
        $('.paginationPopoverContent').append($paginationPopoverClone);
        $paginationPopoverClone.removeClass('hide pagination-popover-origin');
        $paginationPopoverClone.addClass('pagination-popover');

        $('.pageSelectPopover').popover('update')

        $('.paginator-btn').on('click', onPaginationClick);
        // so you can just hit enter after moving the range bar or entering a number
        $('.paginationPageRange').on('keypress', (e) => e.which === 13 && onPaginationClick(e));
        $('.paginationPageText').on('keypress', (e) => e.which === 13 ? onPaginationClick(e) : true);
    });
</script>
