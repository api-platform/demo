import {betterAuth} from "better-auth";
import {genericOAuth} from "better-auth/plugins";
import {nextCookies} from "better-auth/next-js";

import {
  NEXT_PUBLIC_OIDC_CLIENT_ID,
  NEXT_PUBLIC_OIDC_SERVER_URL,
  NEXT_PUBLIC_OIDC_SERVER_URL_INTERNAL
} from "../config/keycloak";

export const auth = betterAuth({
  database: {
    type: "postgres",
    url: process.env.BETTER_AUTH_DATABASE_URL!,
  },
  basePath: "/api/auth",
  secret: process.env.BETTER_AUTH_SECRET,
  user: {modelName: "ba_user"},
  session: {modelName: "ba_session"},
  account: {modelName: "ba_account"},
  verification: {modelName: "ba_verification"},
  plugins: [
    genericOAuth({
      config: [{
        providerId: "keycloak",
        clientId: NEXT_PUBLIC_OIDC_CLIENT_ID,
        clientSecret: process.env.OIDC_CLIENT_SECRET,
        // External URL for browser redirects
        authorizationUrl: `${NEXT_PUBLIC_OIDC_SERVER_URL}/protocol/openid-connect/auth`,
        // Internal URL for server-to-server token exchange
        tokenUrl: `${NEXT_PUBLIC_OIDC_SERVER_URL_INTERNAL}/protocol/openid-connect/token`,
        userInfoUrl: `${NEXT_PUBLIC_OIDC_SERVER_URL_INTERNAL}/protocol/openid-connect/userinfo`,
        scopes: ["openid", "profile", "email", "offline_access"],
        pkce: true,
        accessType: "offline",
        prompt: "consent",
      }],
    }),
    nextCookies(),
  ],
});
