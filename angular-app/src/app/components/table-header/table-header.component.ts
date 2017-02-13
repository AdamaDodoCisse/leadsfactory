import { Component, Input, OnInit, Output, EventEmitter } from '@angular/core';

@Component({
  selector: 'app-table-header',
  templateUrl: './table-header.component.html',
  styleUrls: []
})
export class TableHeaderComponent implements OnInit {

  availablePageSizes = ['5','10','15','50','100'];

  @Input()
  pageSize = "50";

  @Input()
  searchKeyword = '';

  @Output() pageSizeChange: EventEmitter<number> = new EventEmitter<number>();
  @Output() searchKeywordChange: EventEmitter<number> = new EventEmitter<number>();

  constructor() { }

  searchKeword ( keyword ) {
    this.searchKeywordChange.emit( keyword );
  }

  onPageSizeChange (event) {
    console.log (event);
    this.pageSizeChange.emit ( event );
  }

  ngOnInit() {
  }

}
