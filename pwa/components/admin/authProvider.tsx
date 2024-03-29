import { AuthProvider } from "react-admin";
import { signIn } from "next-auth/react";

import { auth, signOut } from "../../app/auth";

const authProvider: AuthProvider = {
  // Nothing to do here, this function will never be called
  login: async () => Promise.resolve(),
  logout: async () => {
    const session = await auth();
    if (!session) {
      return;
    }

    await signOut(session, {callbackUrl: window.location.origin});
  },
  checkError: async (error) => {
    const session = await auth();
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
    const session = await auth();
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
    const session = await auth();

    return session ? Promise.resolve(session.user) : Promise.reject();
  },
  // Nothing to do here, this function will never be called
  handleCallback: () => Promise.resolve(),
};

export default authProvider;
