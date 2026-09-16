import {headers} from "next/headers";

import {auth} from "./auth";

export async function getServerSession() {
  return auth.api.getSession({
    headers: await headers(),
  });
}

export async function getServerAccessToken(): Promise<string | null> {
  try {
    const requestHeaders = await headers();
    // getAccessToken selects the account by its Better Auth row id, so resolve it from the provider first
    const accounts = await auth.api.listUserAccounts({headers: requestHeaders});
    const account = accounts.find(({providerId}) => providerId === "keycloak");

    if (!account) {
      return null;
    }

    const result = await auth.api.getAccessToken({
      body: {accountId: account.id},
      headers: requestHeaders,
    });

    return result?.accessToken ?? null;
  } catch {
    return null;
  }
}
