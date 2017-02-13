import { Component, OnChanges, OnInit, Output, EventEmitter, Input } from '@angular/core';

@Component({
  selector: 'app-table-footer',
  templateUrl: './table-footer.component.html',
  styleUrls: []
})
export class TableFooterComponent implements OnInit {

  currentPage = 1;
  pagination : Array<any> = [];

  @Input()
  numberOfPages = 0;

  @Output() pageChange: EventEmitter<number> = new EventEmitter<number>();

  constructor() {}

  ngOnChanges() {
    this.createPagination(this.currentPage);
  }

  ngOnInit() {}

  onPageChange (value) {
    this.currentPage = value;
    this.createPagination(value);
    this.pageChange.emit (value);
  }

  // Pagination
  createPagination ( currentPage ) {

    this.pagination = [];

    if ( this.numberOfPages > 9 ) {

      // Is this the two first pages ?
      if ( this.currentPage < 7 ) {
        for (var _i = 1; _i <= 7; _i++) {
          this.pagination.push ([_i, '']);
        }
        this.pagination.push (['...', 'disabled']);
        this.pagination.push ([this.numberOfPages-1, '']);
        this.pagination.push ([this.numberOfPages, '']);

      // Is this the two last pages ?
      } else if ( this.currentPage > (this.numberOfPages - 7) ) {

        this.pagination.push (['1', '']);
        this.pagination.push (['2', '']);
        this.pagination.push (['...', 'disabled']);

        for (var _i = 1; _i <= 7; _i++) {
          this.pagination.push ([this.numberOfPages - (7-_i), '']);
        }

      // then display the middle ones...
      } else {

        this.pagination.push (['1', '']);
        this.pagination.push (['2', '']);
        this.pagination.push (['...', 'disabled']);

        this.pagination.push ([this.currentPage-1, '']);
        this.pagination.push ([this.currentPage, 'active']);
        this.pagination.push ([this.currentPage+1, '']);

        this.pagination.push (['...', 'disabled']);
        this.pagination.push ([this.numberOfPages-1, '']);
        this.pagination.push ([this.numberOfPages, '']);

      }

    } else {
      // Add Every pages
      for (var _i = 1; _i <= this.numberOfPages; _i++) {
        this.pagination.push ([_i, currentPage==_i?'active':'' ]);
      }
    }

  }

  // isFirstPage ?
  isFirstPage () {
    return this.currentPage==1? true : false;
  }

  // isLastPage
  isLastPage () {
    return this.currentPage==this.numberOfPages? true:false;
  }

}
