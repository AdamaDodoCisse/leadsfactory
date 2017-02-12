import { Component, OnInit } from '@angular/core';
import { FormsStoresService } from '../../../services/stores/forms-stores.service';

@Component({
  selector: 'app-formulaire-list',
  templateUrl: './formulaire-list.component.html',
  styleUrls: [],
  providers : [FormsStoresService]
})
export class FormulaireListComponent implements OnInit {

  forms = {};
  currentPage = 1;
  availablePageSizes = ['5','10','15','50','100'];
  pageSize = "50";
  searchKeyword = '';
  numberOfPages = 0;
  pagination : Array<any> = [];

  constructor(
      private formStore: FormsStoresService
    ) {
  }

  loadData( data ) {
    this.forms = data;
    this.numberOfPages = data.numberOfPages;
    console.log ("Number of pages : "+this.numberOfPages);
    this.createPagination();
  }

  ngOnInit() {
    this.formStore.getFormList( this.currentPage, this.pageSize, this.searchKeyword ).subscribe ( (data) => {this.loadData(data)} );
  }

  // Pagination
  createPagination ( ) {

    if ( this.numberOfPages > 9 ) {

      // Is this the two first pages ?
      if ( this.currentPage < 7 ) {
        for (var _i = 0; _i < 7; _i++) {
          this.pagination.push (['2', '']);
        }
        this.pagination.push (['...', 'disabled']);
        this.pagination.push ([this.numberOfPages-1, '']);
        this.pagination.push ([this.numberOfPages, '']);

      // Is this the two last pages ?
      } else if ( this.currentPage > (this.numberOfPages - 7) ) {

        this.pagination.push (['1', '']);
        this.pagination.push (['2', '']);
        this.pagination.push (['...', 'disabled']);

        for (var _i = 0; _i < 7; _i++) {
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
      for (var _i = 1; _i < 10; _i++) {
        this.pagination.push ([_i, this.currentPage==_i?'active':'' ]);
      }
    }

  }

  onClick (value) {

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
