"use client";

import {useEffect, useState} from "react";

import {authClient} from "../lib/auth-client";

export const useSession = authClient.useSession;

export function useAccessToken() {
  const session = authClient.useSession();
  const [accessToken, setAccessToken] = useState<string | null>(null);

  useEffect(() => {
    if (!session.data) {
      return;
    }

    let cancelled = false;
    (async () => {
      try {
        const result = await authClient.getAccessToken({
          providerId: "keycloak",
        });
        if (!cancelled) {
          setAccessToken(result.data?.accessToken ?? null);
        }
      } catch {
        if (!cancelled) {
          setAccessToken(null);
        }
      }
    })();

    return () => { cancelled = true; };
  }, [session.data]);

  return {
    accessToken: session.data ? accessToken : null,
    session: session.data,
    isPending: session.isPending,
    error: session.error,
  };
}

export async function signInWithKeycloak(callbackURL?: string) {
  await authClient.signIn.oauth2({
    providerId: "keycloak",
    callbackURL: callbackURL ?? window.location.href,
  });
}

export function signOutWithKeycloak(redirectUri?: string) {
  // Server-side route retrieves idToken from account, revokes session, and redirects to Keycloak logout
  window.location.href = `/api/auth/keycloak-logout?post_logout_redirect_uri=${encodeURIComponent(redirectUri ?? `${window.location.origin}/books`)}`;
}
