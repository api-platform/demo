import {headers} from "next/headers";

import {auth} from "./auth";

export async function getServerSession() {
  return auth.api.getSession({
    headers: await headers(),
  });
}

export async function getServerAccessToken(): Promise<string | null> {
  try {
    const result = await auth.api.getAccessToken({
      body: {providerId: "keycloak"},
      headers: await headers(),
    });

    return result?.accessToken ?? null;
  } catch {
    return null;
  }
}
