<div class="btn-group">
    <button type="button" class="btn btn-info dropdown-toggle btn-xs" data-toggle="dropdown">@lang('messages.actions') <span class="caret"></span></button>
    <ul class="dropdown-menu dropdown-menu-left">
        <li><a href="{{ route('mushak.books.pdf', [$type, $book->id]) }}" target="_blank"><i class="fa fa-file-pdf-o"></i> View PDF</a></li>
        @can('mushak_'.str_replace('-', '_', $type).'.update')
        <li><a href="{{ route('mushak.books.edit', [$type, $book->id]) }}"><i class="fa fa-edit"></i> Edit</a></li>
        @endcan
        @can('mushak_'.str_replace('-', '_', $type).'.delete')
        <li><a href="#" class="delete_mushak" data-href="{{ route('mushak.books.destroy', [$type, $book->id]) }}"><i class="fa fa-trash"></i> Delete</a></li>
        @endcan
    </ul>
</div>
