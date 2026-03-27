import {NextRequest, NextResponse} from "next/server";

import {auth} from "../../../../lib/auth";
import {NEXT_PUBLIC_OIDC_SERVER_URL} from "../../../../config/keycloak";

export async function GET(request: NextRequest) {
  const redirectUri = request.nextUrl.searchParams.get("post_logout_redirect_uri")
    ?? new URL("/books", request.url).toString();

  // Get current session and account to retrieve idToken
  const session = await auth.api.getSession({headers: request.headers});
  let idTokenHint = "";

  if (session) {
    try {
      const accounts = await auth.api.listUserAccounts({headers: request.headers});
      const keycloakAccount = accounts?.find(
        (a: { providerId: string }) => a.providerId === "keycloak"
      );
      if (keycloakAccount && "idToken" in keycloakAccount) {
        idTokenHint = (keycloakAccount as { idToken?: string }).idToken ?? "";
      }
    } catch {
      // Proceed without idToken if account lookup fails
    }

    // Revoke the better-auth session
    await auth.api.signOut({headers: request.headers});
  }

  const logoutUrl = new URL(`${NEXT_PUBLIC_OIDC_SERVER_URL}/protocol/openid-connect/logout`);
  if (idTokenHint) {
    logoutUrl.searchParams.set("id_token_hint", idTokenHint);
  }
  logoutUrl.searchParams.set("post_logout_redirect_uri", redirectUri);

  return NextResponse.redirect(logoutUrl.toString());
}
