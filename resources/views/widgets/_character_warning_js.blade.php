    @if (Auth::check() && Auth::user()->hasPower('manage_characters'))
        <script>
            $(document).ready(function() {
                $.ajax({
                    url: "{{ url('admin/masterlist/get-warnings') }}",
                    type: "GET",
                    dataType: 'json',

                    error: function(data) {
                        console.log('Error getting warnings');
                        console.log(data);
                    },
                    success: function(options) {
                        $('#warningList').selectize({
                            plugins: ["restore_on_backspace", "remove_button"],
                            delimiter: ",",
                            valueField: 'warning',
                            labelField: 'warning',
                            searchField: 'warning',
                            persist: false,
                            create: true,
                            preload: true,
                            options: options,
                            onInitialize: function() {
                                let existingOptions = this.$input.attr('data-init-value') ? JSON.parse(this.$input.attr('data-init-value')) : [];
                                var self = this;
                                if (Object.prototype.toString.call(existingOptions) ===
                                    "[object Array]") {
                                    existingOptions.forEach(function(existingOption) {
                                        self.addOption(existingOption);
                                        self.addItem(existingOption[self.settings
                                            .valueField]);
                                    });
                                } else if (typeof existingOptions === 'object') {
                                    self.addOption(existingOptions);
                                    self.addItem(existingOptions[self.settings.valueField]);
                                }
                            }
                        });
                    },
                });
            });
        </script>
    @endif
