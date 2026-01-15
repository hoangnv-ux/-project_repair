export function checkAuth(to, from, next) {
    const token = localStorage.getItem('admin_token');
    if (!token) {
        next('/login');
    } else {
        next();
    }
  }
