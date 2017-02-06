import { Injectable } from '@angular/core';
import { ConfigService } from '../../services/config.service';

import { Headers, Http, Response, URLSearchParams, RequestOptions } from '@angular/http';
import { Observable } from 'rxjs/Observable';
import 'rxjs/add/operator/catch';
import 'rxjs/add/operator/map';

@Injectable()
export class AuthService {

  configService = null;
  public token: string;

  constructor(private http: Http, configService : ConfigService) {
      // set token if saved in local storage
      var currentUser = JSON.parse(localStorage.getItem('currentUser'));
      this.token = currentUser && currentUser.token;
      this.configService = configService;
  }

  logMe(username: string, password: string)  {
    console.log ("Login request");
    console.log (this.configService.config.backendUrl);

    var headers = new Headers({
         'Content-Type': 'application/x-www-form-urlencoded',
         'Accept': '*/*'});
    var options = new RequestOptions({ headers: headers });

    this.http.post(  this.configService.config.backendUrl +  '/api/login_check',
                              "_username="+username + "&_password="+password, options)
        .map(response =>response)
        .subscribe (response => this.setToken(response, username));

  }

  private setToken (response, username) {
    let token = response.json() && response.json().token;
    if (token) {
        // set token property
        this.token = token;

        // store username and jwt token in local storage to keep user logged in between page refreshes
        localStorage.setItem('currentUser', JSON.stringify({ username: username, token: token }));

        // return true to indicate successful login
        return true;
    } else {
        // return false to indicate failed login
        return false;
    }
  }

  private handleError (error: Response | any) {
    // In a real world app, we might use a remote logging infrastructure
    let errMsg: string;
    if (error instanceof Response) {
      const body = error.json() || '';
      const err = body.error || JSON.stringify(body);
      errMsg = `${error.status} - ${error.statusText || ''} ${err}`;
    } else {
      errMsg = error.message ? error.message : error.toString();
    }
    console.error(errMsg);
    return Observable.throw(errMsg);
  }
  logout(): void {
      // clear token remove user from local storage to log user out
      this.token = null;
      localStorage.removeItem('currentUser');
  }

}
