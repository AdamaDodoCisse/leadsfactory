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
  pageSize = "50";
  searchKeyword = '';
  numberOfPages = 0;

  constructor(
      private formStore: FormsStoresService
    ) {
  }

  loadData( data ) {
    this.forms = data;
    this.numberOfPages = data.numberOfPages;
    this.currentPage = data.query.page;
    //this.createPagination();
  }

  searchKeword ( keyword ) {
    this.formStore.getFormList( this.currentPage, this.pageSize, keyword ).subscribe ( (data) => {this.loadData(data)} );
  }

  onPageSizeChange (event) {
    this.formStore.getFormList( this.currentPage, event, this.searchKeyword ).subscribe ( (data) => {this.loadData(data)} );
  }

  ngOnInit() {
    this.formStore.getFormList( this.currentPage, this.pageSize, this.searchKeyword ).subscribe ( (data) => {this.loadData(data)} );
  }

  onPageChange (value) {
    this.formStore.getFormList( value, this.pageSize, this.searchKeyword ).subscribe ( (data) => {this.loadData(data)} );
  }

}
