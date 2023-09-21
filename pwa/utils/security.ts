import { signOut as logout, type SignOutParams, type SignOutResponse } from "next-auth/react";
import { type Session } from "next-auth";

import { OIDC_SERVER_URL } from "@/config/keycloak";

export async function signOut<R extends boolean = true>(
    session: Session,
    options?: SignOutParams<R>
): Promise<R extends true ? undefined : SignOutResponse> {
  // @ts-ignore
  const url = `${OIDC_SERVER_URL}/protocol/openid-connect/logout?id_token_hint=${session.idToken}&post_logout_redirect_uri=${options?.callbackUrl ?? `${window.location.origin}/books`}`;

  return await logout({ callbackUrl: url });
}
