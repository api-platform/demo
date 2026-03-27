import {NextRequest, NextResponse} from "next/server";
import {Pool} from "pg";

import {auth} from "../../../../lib/auth";
import {NEXT_PUBLIC_OIDC_SERVER_URL} from "../../../../config/keycloak";

const pool = new Pool({
  connectionString: process.env.BETTER_AUTH_DATABASE_URL,
});

export async function GET(request: NextRequest) {
  const redirectUri = request.nextUrl.searchParams.get("post_logout_redirect_uri")
    ?? new URL("/books", request.url).toString();

  const session = await auth.api.getSession({headers: request.headers});
  let idTokenHint = "";

  if (session) {
    // Query ba_account directly to get idToken (not exposed by the API)
    try {
      const result = await pool.query(
        'SELECT "idToken" FROM ba_account WHERE "userId" = $1 AND "providerId" = $2 LIMIT 1',
        [session.user.id, "keycloak"]
      );
      idTokenHint = result.rows[0]?.idToken ?? "";
    } catch {
      // Proceed without idToken if query fails
    }

    await auth.api.signOut({headers: request.headers});
  }

  const logoutUrl = new URL(`${NEXT_PUBLIC_OIDC_SERVER_URL}/protocol/openid-connect/logout`);
  if (idTokenHint) {
    logoutUrl.searchParams.set("id_token_hint", idTokenHint);
  }
  logoutUrl.searchParams.set("post_logout_redirect_uri", redirectUri);

  return NextResponse.redirect(logoutUrl.toString());
}
