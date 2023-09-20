import { signIn, signOut, useSession } from "next-auth/react"
import { useRouter } from "next/router";

import styles from "./header.module.css"

const Header = () => {
  const router = useRouter();
  const { data: session, status } = useSession();
  const loading = status === "loading";

  if (router.pathname === '/' || router.pathname.match(/^\/admin/)) return <></>;

  return (
    <header>
      <noscript>
        <style>{`.nojs-show { opacity: 1; top: 0; }`}</style>
      </noscript>
      <div className={styles.signedInStatus}>
        <p
          className={`nojs-show ${
            !session && loading ? styles.loading : styles.loaded
          }`}
        >
          {session && (
            <a
              href={`/api/auth/signout`}
              className={styles.button}
              onClick={(e) => {
                e.preventDefault()
                signOut()
              }}
            >
              Sign out
            </a>
          ) || (
            <a
              href={`/api/auth/signin`}
              className={styles.buttonPrimary}
              onClick={(e) => {
                e.preventDefault()
                signIn('keycloak')
              }}
            >
              Sign in
            </a>
          )}
        </p>
      </div>
    </header>
  )
}

export default Header;
