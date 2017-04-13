<div class="row">
    <div class="col-sm-12">
        @if ($paginator->hasPages())
            {{-- Pagination Elements --}}
            <div class="row">
                {{-- Previous Page Link --}}
                <div class="btn-page">
                    @if ($paginator->onFirstPage())
                        <span class="btn text-muted">
                            <i class="fa fa-chevron-left"></i>
                        </span>
                    @else
                        <a class="btn btn-waiting" href="{{url('censup'.$paginator->previousPageUrl()) }}" rel="prev">
                            <i class="fa fa-chevron-left"></i>
                        </a>
                    @endif
                </div>
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <span class="text-muted">{{ $element }}</span>
                    @endif
                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="btn text-primary">
                                    <i class="fa fa-file-text-o fa-3x"></i><br/>
                                    {{ $page }}
                                </span>
                            @else
                                <a class="btn btn-waiting text-muted" href="{{ url('censup'.$url) }}">
                                    <i class="fa fa-file-text-o fa-3x"></i><br/>
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
                {{-- Next Page Link --}}
                <div class="btn-page">
                    @if ($paginator->hasMorePages())
                        <a class="btn btn-waiting" href="{{ url('censup'.$paginator->nextPageUrl()) }}" rel="next">
                            <i class="fa fa-chevron-right"></i>
                        </a>
                    @else
                        <span class="btn text-muted">
                            <i class="fa fa-chevron-right"></i>
                        </span>
                    @endif
                </div>
            </div>
        @endif
    </div>
    <div class="col-sm-12">
        <small class="text-muted">Estimativa de {{$paginator->total()/2}} registros</small>
    </div>
</div>
