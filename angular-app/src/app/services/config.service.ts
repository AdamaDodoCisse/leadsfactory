import { Injectable } from '@angular/core';

@Injectable()
export class ConfigService {

  config = {
    backendUrl : "http://local.dev/leadsfactory-v2/web/app_dev.php"
  }

  constructor() { }

}
