"use client";

import { signIn, signOut, useSession } from "next-auth/react";
import { usePathname } from "next/navigation";
import Link from "next/link";
import PersonOutlineIcon from "@mui/icons-material/PersonOutline";
import FavoriteBorderIcon from "@mui/icons-material/FavoriteBorder";

import { OIDC_SERVER_URL } from "../../config/keycloak";

export const Header = () => {
  const pathname = usePathname();
  const { data: session, status } = useSession();

  if (pathname === "/" || pathname.match(/^\/admin/)) return <></>;

  return (
    <header className="bg-neutral-100 sticky top-0 z-10">
      <nav className="container mx-auto flex max-w-7xl items-center justify-between p-6 lg:px-8" aria-label="Global">
        <div className="block text-4xl font-bold">
          <Link href="/books" className="text-gray-700 hover:text-gray-900">
            Books Store
          </Link>
        </div>
        <div className="lg:flex lg:flex-1 lg:justify-end lg:gap-x-12">
          <Link href="/bookmarks" className="font-semibold text-gray-700 hover:text-gray-900 mr-4">
            <FavoriteBorderIcon className="w-6 h-6 mr-1"/>
            My Bookmarks
          </Link>
          {/* @ts-ignore */}
          {status === "authenticated" && (
            <a href="#" className="font-semibold text-gray-900" role="menuitem" onClick={(e) => {
              e.preventDefault();
              signOut({
                // @ts-ignore
                callbackUrl: `${OIDC_SERVER_URL}/protocol/openid-connect/logout?id_token_hint=${session.idToken}&post_logout_redirect_uri=${window.location.origin}/books`,
              });
            }}>
              Sign out
            </a>
          ) || (
            <a href="#" className="font-semibold text-gray-900" role="menuitem" onClick={(e) => {
              e.preventDefault();
              signIn("keycloak");
            }}>
              <PersonOutlineIcon className="w-6 h-6 mr-1"/>
              Log in
            </a>
          )}
        </div>
      </nav>
    </header>
  )
}
