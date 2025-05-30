<x-admin.wrapper>
    <x-slot name="title">
        {{ __('Project Detail') }}
    </x-slot>

    <div class="py-6 space-y-4">
        <div class="bg-white p-6 rounded shadow">
            <h2 class="text-lg font-semibold mb-4">Basic Info</h2>
            <p><strong>Project Code:</strong> {{ $project->project_code }}</p>
            <p><strong>Project Name:</strong> {{ $project->project_name }}</p>
            <p><strong>Developer Name:</strong> {{ $project->developer_name }}</p>
            <p><strong>License No:</strong> {{ $project->license_number }}</p>
            <p><strong>License Valid From:</strong> {{ $project->license_valid_from }}</p>
            <p><strong>License Valid To:</strong> {{ $project->license_valid_to }}</p>
            <p><strong>Permit No:</strong> {{ $project->permit_number }}</p>
            <p><strong>Permit Valid From:</strong> {{ $project->permit_valid_from }}</p>
            <p><strong>Permit Valid To:</strong> {{ $project->permit_valid_to }}</p>
        </div>

        <div class="bg-white p-6 rounded shadow">
            <h2 class="text-lg font-semibold mb-4">Contact Info</h2>
            <p><strong>Email:</strong> {{ $project->email }}</p>
            <p><strong>Phone:</strong> {{ $project->phone }}</p>
            <p><strong>Website:</strong> {{ $project->website }}</p>
        </div>

        <div class="bg-white p-6 rounded shadow">
            <h2 class="text-lg font-semibold mb-4">Location</h2>
            <p><strong>District:</strong> {{ $project->district }}</p>
            <p><strong>State:</strong> {{ $project->state }}</p>
            <p><strong>Registered Address:</strong> {{ $project->registered_address }}</p>
            <p><strong>Business Address:</strong> {{ $project->business_address }}</p>
        </div>

        @php
            // Check if First VP Date is valid
            $firstVPDate = null;
            if (!empty($project->first_vp_date) && strtotime($project->first_vp_date)) {
                $firstVPDate = \Carbon\Carbon::parse($project->first_vp_date);
            }

            // Extract number of months from "22 Bulan"
            preg_match('/\d+/', $project->extension_approved ?? '', $matches);
            $extensionMonths = isset($matches[0]) ? (int) $matches[0] : 0;

            // Calculate new VP date
            $calculatedNewVPDate = $firstVPDate
                ? $firstVPDate->copy()->addMonths($extensionMonths)->format('d M Y')
                : 'N/A';
        @endphp


        <div class="bg-white p-6 rounded shadow overflow-x-auto">
            <h2 class="text-lg font-semibold mb-4">Other Details</h2>
            <p><strong>Agreement Type:</strong> {{ $project->agreement_type }}</p>
            <p><strong>Original Construction Period:</strong> {{ $project->original_construction_period }}</p>
            <p><strong>First SPA Date:</strong> {{ $project->first_pjb_date }}</p>
            <p><strong>First Plan VP Date:</strong> 
                {{ $firstVPDate ? $firstVPDate->format('d M Y') : 'N/A' }}
            </p>
            <p><strong>VP Amendment:</strong> {{ $project->vp_amendment }}</p>
            <p><strong>Extension Approved:</strong> {{ $project->extension_approved }}</p>
            <p><strong>New Construction Period:</strong> {{ $project->new_construction_period }}</p>
            <p><strong>New Plan Vp Date:</strong> {{ $calculatedNewVPDate }}</p>
        </div>

        @php
            $actualValues = $project->unitSummaries->pluck('actual_percentage')->filter()->map(function($value) {
                return floatval(preg_replace('/[^0-9.]/', '', $value));
            });

            $averageActual = $actualValues->count() > 0
                ? number_format($actualValues->avg(), 2) . '%'
                : 'N/A';

            // Min and Max Price Calculation
            $minPrices = $project->unitSummaries->pluck('min_price')->filter()->map(function ($value) {
                return floatval(preg_replace('/[^0-9.]/', '', $value));
            });

            $maxPrices = $project->unitSummaries->pluck('max_price')->filter()->map(function ($value) {
                return floatval(preg_replace('/[^0-9.]/', '', $value));
            });

            $minPriceFormatted = $minPrices->isNotEmpty() ? 'RM' . number_format($minPrices->min(), 0) : 'N/A';
            $maxPriceFormatted = $maxPrices->isNotEmpty() ? 'RM' . number_format($maxPrices->max(), 0) : 'N/A';
        @endphp

        @php
            $cccDates = $project->unitSummaries->pluck('ccc_date')->filter(function ($date) {
                return strtotime($date) !== false;
            })->map(function ($date) {
                return \Carbon\Carbon::parse($date)->timestamp;
            });

            $vpDates = $project->unitSummaries->pluck('vp_date')->filter(function ($date) {
                return strtotime($date) !== false;
            })->map(function ($date) {
                return \Carbon\Carbon::parse($date)->timestamp;
            });

            $finalCccDate = $cccDates->count()
                ? \Carbon\Carbon::createFromTimestamp(round($cccDates->avg()))->format('d-m-Y')
                : 'N/A';

            $finalVpDate = $vpDates->count()
                ? \Carbon\Carbon::createFromTimestamp(round($vpDates->avg()))->format('d-m-Y')
                : 'N/A';
        @endphp

        <div class="bg-white p-6 rounded shadow">
            <h2 class="text-lg font-semibold mb-4">Project Status</h2>
            <div class="space-y-2">
                <p><strong>Maklumat Pembangunan:</strong> {{ $project->development_info ?? '-' }}</p>
                <p><strong>Status Keseluruhan:</strong> {{ $project->overall_status ?? '-' }}</p>
                <p><strong>Actual %:</strong> {{ $averageActual }}</p>
                <p><strong>Minimum Price:</strong> {{ $minPriceFormatted }}</p>
                <p><strong>Maximum Price:</strong> {{ $maxPriceFormatted }}</p>
                <p><strong>Final CCC Date:</strong> {{ $finalCccDate }}</p>
                <p><strong>Final VP Date:</strong> {{ $finalVpDate }}</p>
            </div>
        </div>

        @if(!empty($project->map_url) || !empty($project->brochure_link))
             <div class="bg-white p-6 rounded shadow mt-6">

                @if(!empty($project->brochure_link))
                    <div>
                        <h2 class="text-lg font-semibold mb-2">Brochure</h2>
                        <iframe src="{{ $project->brochure_link }}"
                                class="w-full h-72 rounded border"
                                frameborder="0"
                                allowfullscreen>
                        </iframe>
                    </div>
                @endif

                @if(!empty($project->map_url))
                    <br>
                    <div>
                        <h2 class="text-lg font-semibold mb-2">Map Location</h2>
                        <iframe src="{{ $project->map_url }}"
                                class="w-full h-72 rounded border"
                                frameborder="0"
                                allowfullscreen>
                        </iframe>
                    </div>
                @endif

            </div>
        @endif

        <div class="bg-white p-6 rounded shadow mt-6">
            <h2 class="text-lg font-semibold mb-4">Unit Summaries</h2>
            
            <div class="overflow-x-auto">
                <table class="table w-full min-w-[1000px]">
                    <thead>
                        <tr class="bg-base-200 text-left">
                            <th>House Type</th>
                            <th>Floors</th>
                            <th>Rooms</th>
                            <th>Toilets</th>
                            <th>Built-up Area</th>
                            <th>Unit Count</th>
                            <th>Min Price</th>
                            <th>Max Price</th>
                            <th>Actual %</th>
                            <th>Component Status</th>
                            <th>CCC Date</th>
                            <th>VP Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($project->unitSummaries as $unit)
                            <tr>
                                <td>{{ $unit->house_type }}</td>
                                <td>{{ $unit->floors }}</td>
                                <td>{{ $unit->rooms }}</td>
                                <td>{{ $unit->toilets }}</td>
                                <td>{{ $unit->built_up_area }}</td>
                                <td>{{ $unit->unit_count }}</td>
                                <td>{{ $unit->min_price }}</td>
                                <td>{{ $unit->max_price }}</td>
                                <td>{{ $unit->actual_percentage }}</td>
                                <td>{{ $unit->component_status }}</td>
                                <td>{{ $unit->ccc_date }}</td>
                                <td>{{ $unit->vp_date }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center py-4">No unit summary data found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>


        <div class="bg-white p-6 rounded shadow mt-6">
            <h2 class="text-lg font-semibold mb-4">Unit Box Details</h2>
           <form method="GET" class="flex flex-wrap gap-4 items-center mb-4">
                <div class="flex flex-col">
                    <label class="font-semibold mb-1">No Unit/PT/Lot/Plot</label>
                    <input type="text" name="no_unit" placeholder="No Unit" class="input input-bordered" value="{{ request('no_unit') }}">
                </div>

                <div class="flex flex-col">
                    <label class="font-semibold mb-1">Kuota Bumi</label>
                    <select name="kuota_bumi" class="input input-bordered">
                        <option disabled {{ request('kuota_bumi') == '' ? 'selected' : '' }}>Sila Pilih</option>
                        @foreach ($kuotaBumiOptions as $value)
                            <option value="{{ $value }}" {{ request('kuota_bumi') == $value ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col">
                    <label class="font-semibold mb-1">Status Jualan</label>
                    <select name="status_jualan" class="input input-bordered">
                        <option disabled {{ request('status_jualan') == '' ? 'selected' : '' }}>Sila Pilih</option>
                        @foreach ($statusJualanOptions as $value)
                            <option value="{{ $value }}" {{ request('status_jualan') == $value ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end h-full" style="padding-top: 27px;">
                    <button type="submit" class="btn btn-primary">Cari</button>
                </div>
            </form>

            <div class="mb-4">
                <span class="badge">Jumlah Unit: {{ $totalUnits }}</span>
                <span class="badge bg-green-200">Belum Dijual: {{ $unsoldUnits }}</span>
                <span class="badge bg-yellow-200">Telah Dijual: {{ $soldUnits }}</span>
            </div>

            <table class="table w-full">
                <thead>
                    <tr>
                        @php
                            $sortableColumns = [
                                'no_unit' => 'No. Unit',
                                'no_pt_lot_plot' => 'Lot / Plot',
                                'kuota_bumi' => 'Kuota Bumi',
                                'harga_jualan' => 'Harga Jualan',
                                'harga_spjb' => 'Harga SPJB',
                                'status_jualan' => 'Status Jualan'
                            ];
                        @endphp

                        @foreach ($sortableColumns as $key => $label)
                            <th>
                                <a href="{{ request()->fullUrlWithQuery([
                                    'sort_box_by' => $key,
                                    'sort_box_order' => (request('sort_box_by') == $key && request('sort_box_order') == 'asc') ? 'desc' : 'asc'
                                ]) }}">
                                    {{ $label }}
                                    @if(request('sort_box_by') == $key)
                                        {!! request('sort_box_order') == 'asc' ? '&uarr;' : '&darr;' !!}
                                    @endif
                                </a>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($filteredBoxes as $box)
                        @php
                            $status = strtolower(trim($box->status_jualan));
                            $colorClass = $status === 'belum dijual' ? 'bg-green-100' : '';
                        @endphp
                        <tr class="{{ $colorClass }}">
                            <td>{{ $box->no_unit }}</td>
                            <td>{{ $box->no_pt_lot_plot }}</td>
                            <td>{{ $box->kuota_bumi }}</td>
                            <td>{{ $box->harga_jualan }}</td>
                            <td>{{ $box->harga_spjb }}</td>
                            <td>{{ $box->status_jualan }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6">No filtered unit box data found.</td></tr>
                    @endforelse
                </tbody>
            </table>

        </div>

        <a href="{{ route('admin.view.project.data') }}" class="text-blue-500 underline">← Back to list</a>
    </div>
    
</x-admin.wrapper>
