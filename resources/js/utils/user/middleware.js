export function checkAuth(to, from, next) {
    const token = localStorage.getItem('user_token');
    if (!token) {
        next('/login');
    } else {
        next();
    }
  }
