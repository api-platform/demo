import {AuthProvider} from "react-admin";
import {getSession, signIn, signOut} from "next-auth/react";

import {NEXT_PUBLIC_OIDC_SERVER_URL} from "../../config/keycloak";

const authProvider: AuthProvider = {
  // Nothing to do here, this function will never be called
  login: async () => Promise.resolve(),
  logout: async () => {
    const session = getSession();
    if (!session) {
      return;
    }

    await signOut({
      // @ts-expect-error Ignore Eslint error
      callbackUrl: `${NEXT_PUBLIC_OIDC_SERVER_URL}/protocol/openid-connect/logout?id_token_hint=${session.idToken}&post_logout_redirect_uri=${window.location.origin}`,
    });
  },
  checkError: async (error) => {
    const session = getSession();
    const status = error.status;
    // @ts-expect-error Ignore Eslint error
    if (!session || session?.error === "RefreshAccessTokenError" || status === 401) {
      await signIn("keycloak");

      return;
    }

    if (status === 403) {
      return Promise.reject({ message: "Unauthorized user!", logoutUser: false });
    }
  },
  checkAuth: async () => {
    const session = getSession();
    // @ts-expect-error Ignore Eslint error
    if (!session || session?.error === "RefreshAccessTokenError") {
      await signIn("keycloak");

      return;
    }

    return Promise.resolve();
  },
  getPermissions: () => Promise.resolve(),
  getIdentity: async () => {
    const session = getSession();

    // @ts-expect-error Ignore Eslint error
    return session ? Promise.resolve(session.user) : Promise.reject();
  },
  // Nothing to do here, this function will never be called
  handleCallback: () => Promise.resolve(),
};

export default authProvider;
