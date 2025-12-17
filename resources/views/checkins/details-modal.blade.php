<div class="container-fluid">
    <div class="row">
        <!-- Basic Information -->
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-header bg-light py-2">
                    <h6 class="mb-0"><i class="fa fa-info-circle mr-2"></i>Basic Information</h6>
                </div>
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <strong>Driver:</strong><br>
                            <span class="text-primary">{{ $checkin->driver->name ?? 'N/A' }}</span>
                        </div>
                        <div class="col-md-4 mb-2">
                            <strong>Type:</strong><br>
                            @if($checkin->type == \App\Models\DriverCheckIn::TYPE_CHECK_IN)
                                <span class="badge badge-success">Check In</span>
                            @else
                                <span class="badge badge-warning">Check Out</span>
                            @endif
                        </div>
                        <div class="col-md-4 mb-2">
                            <strong>Check Time:</strong><br>
                            <span class="text-muted">{{ $checkin->check_time ? $checkin->check_time->format('d-m-Y H:i:s') : 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Location Information -->
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-header bg-light py-2">
                    <h6 class="mb-0"><i class="fa fa-map-marker mr-2"></i>Location Information</h6>
                </div>
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <strong>Coordinates:</strong><br>
                            @if($checkin->latitude && $checkin->longitude)
                                <code class="text-primary">{{ number_format($checkin->latitude, 6) }}, {{ number_format($checkin->longitude, 6) }}</code>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </div>
                        <div class="col-md-6 mb-2">
                            <strong>Full Address:</strong><br>
                            <small class="text-muted" style="word-break: break-word; line-height: 1.4;">{{ $fullAddress }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notes (if available) -->
        @if($checkin->notes)
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-header bg-light py-2">
                    <h6 class="mb-0"><i class="fa fa-sticky-note mr-2"></i>Notes</h6>
                </div>
                <div class="card-body p-3">
                    <div class="alert alert-info mb-0 py-2">
                        <i class="fa fa-quote-left mr-1"></i>
                        {{ $checkin->notes }}
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Map Action -->
        @if($checkin->latitude && $checkin->longitude)
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light py-2">
                    <h6 class="mb-0"><i class="fa fa-map mr-2"></i>Map Location</h6>
                </div>
                <div class="card-body p-3 text-center">
                    <a href="https://www.google.com/maps?q={{ $checkin->latitude }},{{ $checkin->longitude }}" 
                       target="_blank" class="btn btn-primary btn-sm">
                        <i class="fa fa-external-link mr-1"></i> Open in Google Maps
                    </a>
                    <small class="d-block text-muted mt-1">Click to view exact location on Google Maps</small>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>