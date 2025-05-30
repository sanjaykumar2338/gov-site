<x-admin.wrapper>
    <x-slot name="title">
        {{ __('Project Data') }}
    </x-slot>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <div class="py-2 px-4">
        <button type="button" id="toggleBtn" class="btn btn-sm btn-secondary mb-4 ml-auto block" onclick="toggleFilterForm()">
            Toggle Filters
        </button>

        <form id="filterForm" method="GET" action="{{ route('admin.view.project.data') }}" class="flex flex-wrap items-center gap-3 mb-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search project or developer..." class="input input-bordered w-full sm:w-[21rem]" />

            <select name="state" class="input input-bordered w-full sm:w-[21rem]">
                <option value="">All States</option>
                @foreach($states as $state)
                    <option value="{{ $state }}" {{ strtolower(request('state')) == strtolower($state) ? 'selected' : '' }}>
                        {{ ucwords($state) }}
                    </option>
                @endforeach
            </select>

            <select name="district" id="district" class="input input-bordered w-full sm:w-[21rem]">
                <option value="">Select District</option>
            </select>

            @php
                $statusOptions = [
                    'Belum Mula',
                    'Lancar',
                    'Lewat',
                    'Sakit',
                    'Siap Dengan CCC',
                    'Siap Dengan CFO',
                    'Permit Telah Dibatalkan',
                ];
            @endphp

            <select name="project_status" class="input input-bordered w-full sm:w-[21rem]">
                <option value="">Selling Status</option>
                @foreach($statusOptions as $status)
                    <option value="{{ $status }}" {{ request('project_status') == $status ? 'selected' : '' }}>
                        {{ $status }}
                    </option>
                @endforeach
            </select>

            <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="Min Price (RM)" class="input input-bordered w-full sm:w-[21rem]">
            <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="Max Price (RM)" class="input input-bordered w-full sm:w-[21rem]">

            <input type="text" name="vp_date_range" id="vp_date_range" class="input input-bordered w-full sm:w-[21rem]"
                placeholder="New First VP Date Range" value="{{ request('vp_date_range') }}">

            <input type="text" name="final_ccc_date" id="final_ccc_date" class="input input-bordered w-full sm:w-[21rem]"
                placeholder="Final CCC Date" value="{{ request('final_ccc_date') }}">

            <input type="text" name="final_vp_date" id="final_vp_date" class="input input-bordered w-full sm:w-[21rem]"
                placeholder="Final VP Date" value="{{ request('final_vp_date') }}">

            <input type="text" name="agreement_type" id="agreement_type" class="input input-bordered w-full sm:w-[21rem]"
                placeholder="Agreement Type" value="{{ request('agreement_type') }}">

            <div class="flex gap-3 items-center ml-auto mt-2 sm:mt-0">
                <button type="submit" class="btn btn-primary">Filter</button>
                <button type="button" class="btn btn-outline" onclick="clearFilters()">Clear Filters</button>
                <button type="button" class="btn btn-secondary" onclick="openColumnOrderModal()">Reorder Columns</button>
            </div>
        </form>


        <div class="w-full overflow-x-auto border border-base-200 shadow rounded-lg">
            <table class="table w-full table-zebra text-sm">
                <thead>
                    <tr class="bg-base-200 text-left">
                        @php
                            $customColumnLabels = array_merge([
                                'new_vp_date' => 'New First VP Date',
                                'first_pjb_date' => 'First SPA Date',
                                'first_vp_date' => 'First Plan VP Date',
                            ], $virtualColumns ?? []);
                        @endphp

                        @foreach($columnOrder as $index => $column)
                            <th class="{{ $loop->first ? 'sticky left-0 bg-base-200 z-10' : '' }}">
                                <a href="{{ request()->fullUrlWithQuery([
                                    'sort_by' => $column,
                                    'sort_order' => (request('sort_by') == $column && request('sort_order') == 'asc') ? 'desc' : 'asc'
                                ]) }}">
                                    {{ $customColumnLabels[$column] ?? ucwords(str_replace('_', ' ', $column)) }}
                                    @if(request('sort_by') == $column)
                                        {!! request('sort_order') == 'asc' ? '&uarr;' : '&darr;' !!}
                                    @endif
                                </a>
                            </th>
                        @endforeach
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($projects as $project)
                        <tr>
                            @foreach($columnOrder as $index => $column)
                                <td class="{{ $loop->first ? 'sticky left-0 bg-white z-0' : '' }}">
                                    @switch($column)
                                        @case('total_units')
                                            {{ $project->unitBoxes->count() }}
                                            @break

                                        @case('total_telah_dijual_units')
                                            {{ $project->unitBoxes->where('status_jualan', 'Telah Dijual')->count() }}
                                            @break

                                        @case('total_belum_dijual_units')
                                            {{ $project->unitBoxes->where('status_jualan', '!=', 'Telah Dijual')->count() }}
                                            @break

                                        @case('new_first_vp_date')
                                            {{ $project->new_vp_date ?? '-' }}
                                            @break

                                        @case('final_ccc_date_virtual')
                                            {{ optional($project->unitSummaries->firstWhere('ccc_date'))?->ccc_date ?? '-' }}
                                            @break

                                        @case('final_vp_date_virtual')
                                            {{ optional($project->unitSummaries->firstWhere('vp_date'))?->vp_date ?? '-' }}
                                            @break

                                        @default
                                            {{ $project->{$column} ?? '-' }}
                                    @endswitch
                                </td>
                            @endforeach
                            <td>
                                <a href="{{ route('admin.view.project.data.show', $project->id) }}"
                                class="btn btn-sm btn-primary">
                                    View Details
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>

            </table>
        </div>

        <div class="py-4">
            {{ $projects->appends(request()->query())->links() }}
        </div>
    </div>

    <!-- Modal -->
    <div id="columnOrderModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white p-6 rounded-lg w-full max-w-md shadow-lg max-h-[90vh] overflow-y-auto">
            <h3 class="text-lg font-semibold mb-4">Reorder & Select Columns</h3>

            <form id="columnOrderForm">
                <ul id="sortableColumns" class="space-y-2">
                    @php
                        $customColumnLabels = array_merge([
                            'new_vp_date' => 'New First VP Date',
                            'first_pjb_date' => 'First SPA Date',
                            'first_vp_date' => 'First Plan VP Date',
                        ], $virtualColumns ?? []);
                    @endphp

                    @foreach($columnOrderData->sortBy('order_index') as $col)
                        <li class="flex items-center gap-2 p-2 bg-gray-100 rounded cursor-move" data-column="{{ $col->column_key }}">
                            <input type="checkbox" class="column-checkbox" value="{{ $col->column_key }}"
                                {{ $col->is_visible ? 'checked' : '' }}>
                            <span>{{ $customColumnLabels[$col->column_key] ?? ucwords(str_replace('_', ' ', $col->column_key)) }}</span>
                        </li>
                    @endforeach
                </ul>
            </form>

            <div class="flex justify-end gap-3 mt-4">
                <button onclick="closeColumnOrderModal()" class="btn">Cancel</button>
                <button onclick="saveColumnOrder()" class="btn btn-primary">Save</button>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div id="toast" class="fixed bottom-5 right-5 bg-green-500 text-white px-4 py-2 rounded shadow hidden">
        ✅ Column order saved!
    </div>
    
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
     <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        function openColumnOrderModal() {
            document.getElementById('columnOrderModal').classList.remove('hidden');
        }

        function closeColumnOrderModal() {
            document.getElementById('columnOrderModal').classList.add('hidden');
        }

        const sortable = new Sortable(document.getElementById('sortableColumns'), {
            animation: 150
        });

        function saveColumnOrder() {
            const selectedColumns = [];
            document.querySelectorAll('#sortableColumns li').forEach((el, index) => {
                const column = el.dataset.column;
                const checked = el.querySelector('input[type="checkbox"]').checked;
                selectedColumns.push({
                    column_key: column,
                    order_index: index,
                    is_visible: checked ? 1 : 0
                });
            });

            fetch('{{ route("admin.save.column.order") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    table_name: 'project_details',
                    columns: selectedColumns
                })
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                closeColumnOrderModal();
                location.reload();
            });
        }

        function toggleFilterForm() {
            const form = document.getElementById('filterForm');
            form.classList.toggle('hidden');
        }

        
        const districtOptions = {
            "johor": [
                "Batu Pahat", "Johor Bahru", "Kluang", "Kota Tinggi", "Kulai", "Mersing", "Muar", "Pontian", "Segamat", "Tangkak"
            ],
            "pulau pinang": [
                "Daerah Barat Daya", "Daerah Timor Laut", "Seberang Perai Selatan", "Seberang Perai Tengah", "Seberang Perai Utara"
            ],
            "selangor": [
                "Gombak", "Hulu Langat", "Hulu Selangor", "Klang", "Kuala Langat", "Kuala Selangor", "Petaling", "Sabak Bernam", "Sepang"
            ],
            "wp kuala lumpur": [
                "Kuala Lumpur"
            ]
        };

        const stateSelect = document.querySelector('select[name="state"]');
        const districtSelect = document.getElementById('district');

        function populateDistricts(selectedState, preselectedDistrict = '') {
            districtSelect.innerHTML = '<option value="">Select District</option>';
            if (districtOptions[selectedState]) {
                districtOptions[selectedState].forEach(function (district) {
                    const option = document.createElement('option');
                    option.value = district;
                    option.textContent = district;
                    if (district.toLowerCase() === preselectedDistrict.toLowerCase()) {
                        option.selected = true;
                    }
                    districtSelect.appendChild(option);
                });
            }
        }

        stateSelect.addEventListener('change', function () {
            populateDistricts(this.value);
        });

        // Auto-select on page load
        document.addEventListener('DOMContentLoaded', function () {
            const preselectedState = "{{ strtolower(request('state')) }}";
            const preselectedDistrict = "{{ request('district') }}";
            if (preselectedState) {
                populateDistricts(preselectedState, preselectedDistrict);
            }
        });

        $(function () {
            // VP Date Range
            $('input[name="vp_date_range"]').daterangepicker({
                autoUpdateInput: false,
                locale: { format: 'YYYY-MM-DD' }
            });

            $('input[name="vp_date_range"]').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
            });

            $('input[name="vp_date_range"]').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
            });

            // Final CCC Date
            $('input[name="final_ccc_date"]').daterangepicker({
                autoUpdateInput: false,
                locale: { format: 'YYYY-MM-DD' }
            });

            $('input[name="final_ccc_date"]').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
            });

            $('input[name="final_ccc_date"]').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
            });

            // Final VP Date
            $('input[name="final_vp_date"]').daterangepicker({
                autoUpdateInput: false,
                locale: { format: 'YYYY-MM-DD' }
            });

            $('input[name="final_vp_date"]').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
            });

            $('input[name="final_vp_date"]').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
            });
        });

        function clearFilters() {
            // Clear local input value
            const dateRangeInput = document.getElementById('vp_date_range');
            if (dateRangeInput) {
                dateRangeInput.value = '';
            }

            document.querySelectorAll('#filterForm input, #filterForm select, #filterForm textarea, #filterForm text').forEach(el => {
                el.value = '';
            });

            // Redirect to the base URL without query strings
            window.location.href = "{{ route('admin.view.project.data') }}";
        }

         function toggleFilterForm() {
            const form = document.getElementById('filterForm');
            form.classList.toggle('hidden');
            localStorage.setItem('filterFormVisible', !form.classList.contains('hidden'));
        }

        function clearFilters() {
            const form = document.getElementById('filterForm');
            form.reset();
            document.getElementById('vp_date_range').value = '';
            document.getElementById('final_ccc_date').value = '';
            document.getElementById('final_vp_date').value = '';
            form.submit();
        }

        // Maintain toggle filter state on page load
        document.addEventListener('DOMContentLoaded', function () {
            const filterFormVisible = localStorage.getItem('filterFormVisible');
            if (filterFormVisible === 'false') {
                document.getElementById('filterForm').classList.add('hidden');
            }
        });
    </script>
</x-admin.wrapper>