import { AUTH_LOGIN, AUTH_LOGOUT, AUTH_ERROR, AUTH_CHECK } from 'react-admin';

export default (type, {username, password, status}) => {
  switch (type) {
    case AUTH_LOGIN:
      const request = new Request(`${process.env.REACT_APP_API_ENTRYPOINT}/authentication_token`, {
        method: 'POST',
        body: JSON.stringify({ email: username, password }),
        headers: new Headers({ 'Content-Type': 'application/json' }),
      });

      return fetch(request)
        .then(response => {
          if (response.status < 200 || response.status >= 300) throw new Error(response.statusText);

          return response.json();
        })
        .then(({ token }) => {
          localStorage.setItem('token', token); // The JWT token is stored in the browser's local storage
          window.location.replace('/');
        });

    case AUTH_LOGOUT:
      localStorage.removeItem('token');
      break;

    case AUTH_ERROR:
      if (401 === status || 403 === status) {
        localStorage.removeItem('token');

        return Promise.reject();
      }
      break;

    case AUTH_CHECK:
      return localStorage.getItem('token') ? Promise.resolve() : Promise.reject();

    default:
  }

  return Promise.resolve();
}
