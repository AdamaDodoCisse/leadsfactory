import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { HttpModule } from '@angular/http';
import { RouterModule, Routes } from '@angular/router';

import { AppComponent } from './app.component';
import { NavigationBarComponent } from './components/navigation-bar/navigation-bar.component';
import { MainContentComponent } from './components/main-content/main-content.component';
import { Test1Component } from './test1/test1.component';
import { FormulaireListComponent } from './components/formulaires/formulaire-list/formulaire-list.component';

const appRoutes: Routes = [
  { path: '', component: MainContentComponent },
  { path: 'test', component: Test1Component },
];

@NgModule({
  declarations: [
    AppComponent,
    NavigationBarComponent,
    MainContentComponent,
    Test1Component,
    FormulaireListComponent
  ],
  imports: [
    BrowserModule,
    FormsModule,
    HttpModule,
    RouterModule.forRoot(appRoutes)
  ],
  providers: [],
  bootstrap: [AppComponent]
})
export class AppModule { }
