export function getRolesFromAccessToken(accessToken: string): string[] {
  try {
    const payload = JSON.parse(
      atob(accessToken.split(".")[1].replace(/-/g, "+").replace(/_/g, "/"))
    );
    return payload?.realm_access?.roles ?? [];
  } catch {
    return [];
  }
}
