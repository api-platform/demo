"use client";

import {Box, Typography, Button} from "@mui/material";
import LockIcon from "@mui/icons-material/Lock";

import {signOutWithKeycloak} from "../../hooks/useAuth";

const AccessDenied = () => (
  <Box display="flex" flexDirection="column" alignItems="center" justifyContent="center" height="100vh" gap={2}>
    <LockIcon sx={{fontSize: 60, color: "text.secondary"}}/>
    <Typography variant="h5" component="h1">
      Access Denied
    </Typography>
    <Typography variant="body1" color="text.secondary">
      You do not have permission to access the administration panel.
    </Typography>
    <Box display="flex" gap={2}>
      <Button
        variant="contained"
        href="/books"
      >
        Browse books
      </Button>
      <Button
        variant="outlined"
        onClick={() => signOutWithKeycloak(window.location.origin)}
      >
        Sign out
      </Button>
    </Box>
  </Box>
);

export default AccessDenied;
