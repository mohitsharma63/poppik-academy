
import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class AdminService {
  // Build an absolute backend base so the app can call PHP directly without relying on dev-server proxy.
  // Use live backend URL for production
  private apiUrl = 'https://backend.poppikacademy.com';

  constructor(private http: HttpClient) {
    // Always use live backend URL
    this.apiUrl = 'https://backend.poppikacademy.com';
  }

  // Authentication
  login(email: string, password: string): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/login.php`, { email, password });
  }

  getAdminId(): number | null {
    try {
      if (typeof window === 'undefined' || typeof localStorage === 'undefined') return null;
      const adminId = localStorage.getItem('adminId');
      console.log('[AdminService] getAdminId():', adminId);
      return adminId ? parseInt(adminId, 10) : null;
    } catch (e) {
      return null;
    }
  }

  setAdminId(adminId: number): void {
    console.log('[AdminService] setAdminId():', adminId);
    try {
      if (typeof window === 'undefined' || typeof localStorage === 'undefined') return;
      // Store adminId for app usage
      localStorage.setItem('adminId', adminId.toString());
      // Also set a token flag expected by AdminAuthGuard so route guards allow access
      localStorage.setItem('adminToken', adminId.toString());
    } catch (e) {}
  }

  isLoggedIn(): boolean {
    try {
      // Consider either an adminId or an adminToken as logged-in indicators
      if (this.getAdminId() !== null) return true;
      if (typeof window === 'undefined') return false;
      const token = (typeof localStorage !== 'undefined' && localStorage.getItem('adminToken')) || (typeof sessionStorage !== 'undefined' && sessionStorage.getItem('adminToken'));
      return !!token;
    } catch (e) {
      return false;
    }
  }

  clearAdminSession(): void {
    try {
      if (typeof window === 'undefined') return;
      localStorage.removeItem('adminId');
      // Remove the token flag too
      localStorage.removeItem('adminToken');
      sessionStorage.removeItem('adminToken');
    } catch (e) {}
  }

  // Dashboard Stats
  getStats(): Observable<any> {
    return this.http.get(`${this.apiUrl}/api/stats.php`);
  }

  // Courses
  getCourses(): Observable<any> {
    return this.http.get(`${this.apiUrl}/api/courses.php`);
  }

  addCourse(course: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/courses.php`, course);
  }

  updateCourse(id: number, course: any): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/courses.php?id=${id}`, course);
  }

  deleteCourse(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/api/courses.php?id=${id}`);
  }

  // Students
  getStudents(): Observable<any> {
    return this.http.get(`${this.apiUrl}/api/students.php`);
  }

  addStudent(student: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/students.php`, student);
  }

  updateStudent(id: number, student: any): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/students.php?id=${id}`, student);
  }

  deleteStudent(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/api/students.php?id=${id}`);
  }

  // Certificates
  getCertificates(): Observable<any> {
    return this.http.get(`${this.apiUrl}/api/certificates.php`);
  }

  addCertificate(data: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/certificates.php`, data);
  }

  deleteCertificate(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/api/certificates.php?id=${id}`);
  }

  // Queries
  getQueries(): Observable<any> {
    return this.http.get(`${this.apiUrl}/api/queries.php`);
  }

  addQuery(query: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/queries.php`, query);
  }

  updateQueryStatus(id: number, status: string): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/queries.php?id=${id}`, { status });
  }

  // Hero Sliders
  getHeroSliders(): Observable<any> {
    return this.http.get(`${this.apiUrl}/api/hero-sliders.php`);
  }

  addHeroSlider(slider: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/hero-sliders.php`, slider);
  }

  updateHeroSlider(id: number, slider: any): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/hero-sliders.php?id=${id}`, slider);
  }

  deleteHeroSlider(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/api/hero-sliders.php?id=${id}`);
  }

  // Gallery
  getGallery(): Observable<any> {
    return this.http.get(`${this.apiUrl}/api/gallery.php`);
  }

  addGalleryImage(image: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/gallery.php`, image);
  }

  // Upload an image file for the gallery using multipart/form-data
  uploadGalleryFile(formData: FormData): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/gallery.php?upload=1`, formData);
  }

  updateGalleryImage(id: number, image: any): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/gallery.php?id=${id}`, image);
  }

  deleteGalleryImage(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/api/gallery.php?id=${id}`);
  }

  // Blogs
  getBlogs(): Observable<any> {
    return this.http.get(`${this.apiUrl}/api/blogs.php`);
  }

  addBlog(blog: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/blogs.php`, blog);
  }

  updateBlog(id: number, blog: any): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/blogs.php?id=${id}`, blog);
  }

  deleteBlog(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/api/blogs.php?id=${id}`);
  }

  // Videos
  getVideos(): Observable<any> {
    return this.http.get(`${this.apiUrl}/api/videos.php`);
  }

  addVideo(video: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/videos.php`, video);
  }

  updateVideo(id: number, video: any): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/videos.php?id=${id}`, video);
  }

  deleteVideo(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/api/videos.php?id=${id}`);
  }

  // Partners
  getPartners(): Observable<any> {
    return this.http.get(`${this.apiUrl}/api/partners.php`);
  }

  addPartner(partner: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/partners.php`, partner);
  }

  updatePartner(id: number, partner: any): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/partners.php?id=${id}`, partner);
  }

  deletePartner(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/api/partners.php?id=${id}`);
  }

  // Settings
  getSettings(): Observable<any> {
    return this.http.get(`${this.apiUrl}/api/settings.php`);
  }

  updateSettings(settings: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/settings.php`, settings);
  }

  // Change password
  changePassword(payload: { currentPassword: string; newPassword: string; }): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/change-password.php`, payload);
  }
}
