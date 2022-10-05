// @ts-ignore
import PropTypes from 'prop-types';
import { styled } from '@mui/material/styles';
import { Box, Button, CardContent, CircularProgress } from '@mui/material';
import {
  Form,
  TextInput,
  required,
  useTranslate,
  useLogin,
  useNotify,
  useSafeSetState,
} from 'react-admin';

export const LoginForm = (props: LoginFormProps) => {
  const { redirectTo, className } = props;
  const [loading, setLoading] = useSafeSetState(false);
  const login = useLogin();
  const translate = useTranslate();
  const notify = useNotify();

  const submit = (values: FormData) => {
    setLoading(true);
    login(values, redirectTo)
      .then(() => {
        setLoading(false);
      })
      .catch((error) => {
        setLoading(false);
        notify(
          typeof error === 'string'
            ? error
            : typeof error === 'undefined' || !error.message
            ? 'ra.auth.sign_in_error'
            : error.message,
          {
            type: 'warning',
            messageArgs: {
              _:
                typeof error === 'string'
                  ? error
                  : error && error.message
                  ? error.message
                  : undefined,
            },
          },
        );
      });
  };

  return (
    <StyledForm
      // @ts-ignore
      onSubmit={submit}
      mode="onChange"
      noValidate
      className={className}
      defaultValues={{ username: 'admin@example.com', password: 'admin' }}>
      <CardContent className={LoginFormClasses.content}>
        <Box className={LoginFormClasses.hint}>
          Hint: admin@example.com / admin
        </Box>
        <TextInput
          name="username"
          autoFocus
          source="username"
          label={translate('ra.auth.username')}
          validate={required()}
          fullWidth
        />
        <TextInput
          name="password"
          source="password"
          label={translate('ra.auth.password')}
          type="password"
          autoComplete="current-password"
          validate={required()}
          fullWidth
        />

        <Button
          variant="contained"
          type="submit"
          color="primary"
          disabled={loading}
          fullWidth
          className={LoginFormClasses.button}>
          {loading ? (
            <CircularProgress
              className={LoginFormClasses.icon}
              size={19}
              thickness={3}
            />
          ) : (
            translate('ra.auth.sign_in')
          )}
        </Button>
      </CardContent>
    </StyledForm>
  );
};

const PREFIX = 'RaLoginForm';

export const LoginFormClasses = {
  content: `${PREFIX}-content`,
  button: `${PREFIX}-button`,
  icon: `${PREFIX}-icon`,
  hint: `${PREFIX}-hint`,
};

const StyledForm = styled(Form, {
  name: PREFIX,
  overridesResolver: (props, styles) => styles.root,
})(({ theme }) => ({
  [`& .${LoginFormClasses.content}`]: {
    width: 300,
  },
  [`& .${LoginFormClasses.button}`]: {
    marginTop: theme.spacing(2),
  },
  [`& .${LoginFormClasses.icon}`]: {
    margin: theme.spacing(0.3),
  },
  [`& .${LoginFormClasses.hint}`]: {
    marginBottom: theme.spacing(1),
    display: 'flex',
    justifyContent: 'center',
    color: theme.palette.grey[500],
  },
}));

export interface LoginFormProps {
  redirectTo?: string;
  className?: string;
}

interface FormData {
  username: string;
  password: string;
}
LoginForm.propTypes = {
  redirectTo: PropTypes.string,
};
