import { Component, OnInit } from '@angular/core';
import { NgForm } from '@angular/forms';
import { AuthService } from '../../services/auth/auth.service';
import { ConfigService } from '../../services/config.service';

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  providers : [AuthService, ConfigService]
})
export class LoginComponent implements OnInit {

  authService = null;
  configService = null;

  constructor( authService : AuthService , configService : ConfigService) {
    this.authService = authService;
    this.configService = configService;
  }

  ngOnInit() {
  }

  onSubmit( form: NgForm) {
    console.log ("Login :" + form.value.username );
    this.authService.logMe ( form.value.username, form.value.password );
  }

}
