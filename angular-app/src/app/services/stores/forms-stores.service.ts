import { Injectable } from '@angular/core';

import { HttpService } from '../http.service';
import { AuthService } from '../auth/auth.service';
import { ConfigService } from '../../services/config.service';

@Injectable()
export class FormsStoresService {

  constructor(
      private http: HttpService,
      private authenticationService: AuthService,
      private configService : ConfigService) {
  }

  getFormList ( page, limit, keyword ) {

    console.log ("Call from getFormList");

    // get users from api
    if (keyword != '') {
      return this.http.get ( '/admin/entity/form/list/'+page+'/'+limit+'/'+keyword );
    } else {
      return this.http.get ( '/admin/entity/form/list/'+page+'/'+limit );
    }


  }

}
