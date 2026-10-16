{{--
    Progressive-enhancement typeahead for patient <select> pickers.

    These pickers live inside POST forms, so they cannot carry their own
    GET search form. This partial renders a small search box that filters the
    matching select(s) via an ajax endpoint. Without JS the prefetched option
    list still works.

    Required: $id, $endpoint, $targets (array of CSS selectors)
    Optional: $placeholder, $blankLabel
--}}
<div class="mb-2">
    <input type="search"
           id="{{ $id }}"
           class="form-control form-control-sm patient-picker-input"
           placeholder="{{ $placeholder ?? 'Search patients…' }}"
           data-endpoint="{{ $endpoint }}"
           data-targets="{{ implode(',', $targets) }}"
           data-blank="{{ $blankLabel ?? 'Patient…' }}"
           autocomplete="off">
</div>

@once
<script>
(function () {
    document.querySelectorAll('.patient-picker-input').forEach(function (input) {
        var timer = null;
        var cache = {};

        input.addEventListener('input', function () {
            clearTimeout(timer);
            timer = setTimeout(function () { run(input); }, 250);
        });

        function run(input) {
            var q = input.value.trim();
            var endpoint = input.dataset.endpoint;
            var key = endpoint + '|' + q;

            if (cache[key]) {
                apply(input, cache[key]);
                return;
            }

            fetch(endpoint + (q ? '?q=' + encodeURIComponent(q) : ''), {
                headers: { 'Accept': 'application/json' }
            })
                .then(function (response) { return response.json(); })
                .then(function (json) {
                    cache[key] = json.data || [];
                    apply(input, cache[key]);
                })
                .catch(function () { /* keep the existing options on failure */ });
        }

        function apply(input, rows) {
            var blank = input.dataset.blank;
            input.dataset.targets.split(',').forEach(function (selector) {
                document.querySelectorAll(selector.trim()).forEach(function (select) {
                    var selected = select.value;

                    select.innerHTML = '';
                    var empty = document.createElement('option');
                    empty.value = '';
                    empty.textContent = blank;
                    select.appendChild(empty);

                    rows.forEach(function (row) {
                        var option = document.createElement('option');
                        option.value = row.id;
                        option.textContent = row.name;
                        select.appendChild(option);
                    });

                    if (selected) {
                        select.value = selected;
                    }
                });
            });
        }
    });
})();
</script>
@endonce
