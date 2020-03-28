import React from "react";
import PropTypes from "prop-types";
import { Field, Form } from "react-final-form";

import Button from "@material-ui/core/Button";
import CardActions from "@material-ui/core/CardActions";
import CircularProgress from "@material-ui/core/CircularProgress";
import TextField from "@material-ui/core/TextField";
import { makeStyles } from "@material-ui/core/styles";

import { Login as BaseLogin } from "react-admin";
import { useTranslate, useLogin, useNotify, useSafeSetState } from "ra-core";

const useStyles = makeStyles((theme) => ({
  hint: {
    marginTop: "1em",
    display: "flex",
    justifyContent: "center",
    color: theme.palette.grey[500],
  },
  form: {
    padding: "0 1em 1em 1em",
  },
  input: {
    marginTop: "1em",
  },
  button: {
    width: "100%",
  },
  icon: {
    marginRight: theme.spacing(1),
  },
}));

const Input = ({ meta: { touched, error }, input: inputProps, ...props }) => (
  <TextField
    error={!!(touched && error)}
    helperText={touched && error}
    {...inputProps}
    {...props}
    fullWidth
  />
);

const Login = (props) => {
  const { redirectTo } = props;
  const [loading, setLoading] = useSafeSetState(false);
  const login = useLogin();
  const translate = useTranslate();
  const notify = useNotify();
  const classes = useStyles(props);

  const validate = (values) => {
    const errors = { username: undefined, password: undefined };

    if (!values.username) {
      errors.username = translate("ra.validation.required");
    }
    if (!values.password) {
      errors.password = translate("ra.validation.required");
    }
    return errors;
  };

  const submit = (values) => {
    setLoading(true);
    login(values, redirectTo)
      .then(() => {
        setLoading(false);
      })
      .catch((error) => {
        setLoading(false);
        notify(
          typeof error === "string"
            ? error
            : typeof error === "undefined" || !error.message
            ? "ra.auth.sign_in_error"
            : error.message,
          "warning"
        );
      });
  };

  return (
    <BaseLogin {...props}>
      <Form
        onSubmit={submit}
        validate={validate}
        render={({ handleSubmit }) => (
          <form onSubmit={handleSubmit} noValidate>
            <div className={classes.hint}>Hint: admin@example.com / admin</div>
            <div className={classes.form}>
              <div className={classes.input}>
                <Field
                  autoFocus
                  id="username"
                  name="username"
                  component={Input}
                  label={translate("ra.auth.username")}
                  disabled={loading}
                />
              </div>
              <div className={classes.input}>
                <Field
                  id="password"
                  name="password"
                  component={Input}
                  label={translate("ra.auth.password")}
                  type="password"
                  disabled={loading}
                  autoComplete="current-password"
                />
              </div>
            </div>
            <CardActions>
              <Button
                variant="contained"
                type="submit"
                color="primary"
                disabled={loading}
                className={classes.button}
              >
                {loading && (
                  <CircularProgress
                    className={classes.icon}
                    size={18}
                    thickness={2}
                  />
                )}
                {translate("ra.auth.sign_in")}
              </Button>
            </CardActions>
          </form>
        )}
      />
    </BaseLogin>
  );
};

Login.propTypes = {
  authProvider: PropTypes.func,
  previousRoute: PropTypes.string,
};

export default Login;
