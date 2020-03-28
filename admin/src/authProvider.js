export default {
  login: ({ username, password }) => {
    const request = new Request(
      `${process.env.REACT_APP_API_ENTRYPOINT}/authentication_token`,
      {
        method: "POST",
        body: JSON.stringify({ email: username, password }),
        headers: new Headers({ "Content-Type": "application/json" }),
      }
    );
    return fetch(request)
      .then((response) => {
        if (response.status < 200 || response.status >= 300) {
          throw new Error(response.statusText);
        }
        return response.json();
      })
      .then(({ token }) => {
        localStorage.setItem("token", token);
      });
  },
  logout: () => {
    localStorage.removeItem("token");
    localStorage.removeItem("authentication");
    return Promise.resolve();
  },
  checkAuth: () =>
    !localStorage.getItem("authentication") || localStorage.getItem("token")
      ? Promise.resolve()
      : Promise.reject(),
  checkLogged: () => !!localStorage.getItem("token"),
  checkError: (error) => {
    const status = error.status;
    if (status === 401 || status === 403) {
      localStorage.removeItem("token");
      return Promise.reject();
    }
    return Promise.resolve();
  },
  getPermissions: () => Promise.resolve(),
};
