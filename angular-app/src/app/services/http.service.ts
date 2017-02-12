import { Injectable } from '@angular/core';
import {Http, Headers, URLSearchParams, RequestOptions, Request, RequestMethod} from "@angular/http"
import { Observable } from 'rxjs';

import { AuthService } from './auth/auth.service';
import { ConfigService } from './config.service';

@Injectable()
export class HttpService {

  constructor(
      private http: Http,
      private authenticationService: AuthService,
      private configService : ConfigService) {
  }

  get ( path: string ) {

    // add authorization header with jwt token
    let headers = new Headers({
      'Authorization': 'Bearer ' + this.authenticationService.token,
      'Accept': 'application/json'
    });

    let options = new RequestOptions({ headers: headers });

    this.checkAuthorised();
    let url: string = this.configService.config.backendUrl + path;

    return this.intercept(this.http.get(url, options).map(res => res.json()));

  }

  intercept (observable: Observable<any>)
  {
      return observable.catch(err =>
      {

          if (err.status === 401)
          {
              console.log ("401 : UnAuthorised!")!
              return this.unauthorised();

          } else if (err.status === 403)
          {
              return this.forbidden();
          } else
          {
              return Observable.throw(err);
          }
      });
  }

  unauthorised (): Observable<any>
  {
      this.authenticationService.redirectToLogin();
      return Observable.empty();
  }

  forbidden (): Observable<any>
  {
      //this.router.navigate(['/']);
      return Observable.empty();
  }

  checkAuthorised (): void
  {
    /*
      if (!this.token.token.length)
      {
          this.router.navigate(['login']);
      }
      */
  }

}
