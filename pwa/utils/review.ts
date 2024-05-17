import { useEffect, useState } from "react";

import { type Session } from "../app/auth";
import { type Review } from "../types/Review";
import { NEXT_PUBLIC_OIDC_AUTHORIZATION_CLIENT_ID, NEXT_PUBLIC_OIDC_SERVER_URL } from "../config/keycloak";

interface Permission {
  result: boolean;
}

export const usePermission = (review: Review, session: Session|null): boolean => {
  const [isGranted, grant] = useState<boolean>(false);

  useEffect(() => {
    if (!session) {
      return;
    }

    (async () => {
      try {
        const response = await fetch(`${NEXT_PUBLIC_OIDC_SERVER_URL}/protocol/openid-connect/token`, {
          headers: {
            "Content-Type": "application/x-www-form-urlencoded",
            Authorization: `Bearer ${session?.accessToken}`,
          },
          body: new URLSearchParams({
            grant_type: "urn:ietf:params:oauth:grant-type:uma-ticket",
            audience: NEXT_PUBLIC_OIDC_AUTHORIZATION_CLIENT_ID,
            response_mode: "decision",
            permission_resource_format: "uri",
            permission_resource_matching_uri: "true",
            // @ts-ignore
            permission: review["@id"].toString(),
          }),
          method: "POST",
        });
        const permission: Permission = await response.json();

        if (permission.result) {
          grant(true);
        }
      } catch (error) {
        console.error(error);
        grant(false);
      }
    })();
  }, [review]);

  return isGranted;
};
