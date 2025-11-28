import { Routes } from '@angular/router';
import { PoliciesComponent } from './policies/policies.component';
import { HomeComponent } from './home/home.component';

export const routes: Routes = [
  { path: '', component: HomeComponent },
  { path: 'policies', component: PoliciesComponent }
];
