import { AuthProvider } from "react-admin";
import { signIn, signOut, useSession } from "next-auth/react";

import { OIDC_SERVER_URL } from "../../config/keycloak";

const authProvider: AuthProvider = {
  // Nothing to do here, this function will never be called
  login: async () => Promise.resolve(),
  logout: async () => {
    // eslint-disable-next-line react-hooks/rules-of-hooks
    const { data: session } = useSession();
    if (!session) {
      return;
    }

    await signOut({
      // @ts-ignore
      callbackUrl: `${OIDC_SERVER_URL}/protocol/openid-connect/logout?id_token_hint=${session.idToken}&post_logout_redirect_uri=${window.location.origin}`,
    });
  },
  checkError: async (error) => {
    // eslint-disable-next-line react-hooks/rules-of-hooks
    const { data: session } = useSession();
    const status = error.status;
    // @ts-ignore
    if (!session || session?.error === "RefreshAccessTokenError" || status === 401) {
      await signIn("keycloak");

      return;
    }

    if (status === 403) {
      return Promise.reject({ message: "Unauthorized user!", logoutUser: false });
    }
  },
  checkAuth: async () => {
    // eslint-disable-next-line react-hooks/rules-of-hooks
    const { data: session } = useSession();
    // @ts-ignore
    if (!session || session?.error === "RefreshAccessTokenError") {
      await signIn("keycloak");

      return;
    }

    return Promise.resolve();
  },
  getPermissions: () => Promise.resolve(),
  // @ts-ignore
  getIdentity: async () => {
    // eslint-disable-next-line react-hooks/rules-of-hooks
    const { data: session } = useSession();

    return session ? Promise.resolve(session.user) : Promise.reject();
  },
  // Nothing to do here, this function will never be called
  handleCallback: () => Promise.resolve(),
};

export default authProvider;
