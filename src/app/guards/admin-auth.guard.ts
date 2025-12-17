import { Injectable } from '@angular/core';
import { Router, CanActivate, ActivatedRouteSnapshot, RouterStateSnapshot } from '@angular/router';

@Injectable({
  providedIn: 'root'
})
export class AdminAuthGuard implements CanActivate {
  constructor(private router: Router) {}

  canActivate(route: ActivatedRouteSnapshot, state: RouterStateSnapshot): boolean {
    // During SSR/prerender we don't have window/localStorage; allow navigation so prerender can proceed
    if (typeof window === 'undefined') return true;

    // Check if admin is logged in (check localStorage or sessionStorage for auth token)
    let adminToken: string | null = null;
    try {
      const ls = (typeof localStorage !== 'undefined') ? localStorage.getItem('adminToken') : null;
      const ss = (typeof sessionStorage !== 'undefined') ? sessionStorage.getItem('adminToken') : null;
      adminToken = ls || ss;
    } catch (e) { adminToken = null; }

    if (adminToken) {
      return true; // User is logged in, allow access
    }

    // User is not logged in, redirect to admin login
    this.router.navigate(['/admin/login']);
    return false;
  }
}
