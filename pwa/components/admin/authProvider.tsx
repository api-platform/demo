import {AuthProvider} from "react-admin";

import {authClient} from "../../lib/auth-client";
import {signInWithKeycloak, signOutWithKeycloak} from "../../hooks/useAuth";

const authProvider: AuthProvider = {
  // Nothing to do here, this function will never be called
  login: async () => Promise.resolve(),
  logout: async () => {
    await signOutWithKeycloak(window.location.origin);
  },
  checkError: async (error) => {
    const status = error.status;
    if (status === 401) {
      await signInWithKeycloak();

      return;
    }

    if (status === 403) {
      return Promise.reject({ message: "Unauthorized user!", logoutUser: false });
    }
  },
  checkAuth: async () => {
    const { data: session } = await authClient.getSession();
    if (!session) {
      await signInWithKeycloak();

      return;
    }

    return Promise.resolve();
  },
  getPermissions: () => Promise.resolve(),
  getIdentity: async () => {
    const { data: session } = await authClient.getSession();

    return session ? Promise.resolve(session.user) : Promise.reject();
  },
  // Nothing to do here, this function will never be called
  handleCallback: () => Promise.resolve(),
};

export default authProvider;
