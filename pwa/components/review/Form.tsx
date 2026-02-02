import {type FunctionComponent} from "react";
import {Formik} from "formik";
import * as Yup from "yup";
import {useMutation} from "@tanstack/react-query";
import {FormGroup, TextareaAutosize} from "@mui/material";
import Rating from "@mui/material/Rating";

import {fetchApi} from "../../utils/dataAccess";
import {type Book} from "../../types/Book";
import {type Review} from "../../types/Review";
import {useSession} from "next-auth/react";

interface Props {
  book: Book;
  onSuccess?: (review: Review) => void;
  review?: Review;
  username: string;
}

const DisplayingErrorMessagesSchema = Yup.object().shape({
  rating: Yup.number().required('Required'),
  body: Yup.string().required('Required'),
});

export const Form: FunctionComponent<Props> = ({ book, onSuccess, review, username }) => {
  const { data: session } = useSession();
  const saveReview = async (values: Review) =>
    await fetchApi<Review>(!values["@id"] ? `${book["@id"]}/reviews` : values["@id"], {
      method: !values["@id"] ? "POST" : "PATCH",
      body: JSON.stringify(values),
      // @ts-expect-error Ignore Eslint error
    }, session);

  const saveMutation = useMutation({
    mutationFn: async (values: Review) => saveReview(values),
    onError: (error) => {
      console.error(error);
    },
  });

  return (
    <Formik
      initialValues={review ?? {}}
      validationSchema={DisplayingErrorMessagesSchema}
      enableReinitialize={true}
      // @ts-expect-error Ignore Eslint error
      onSubmit={(values: Review, { setSubmitting, setStatus, setErrors, resetForm }) => {
        saveMutation.mutate(
          {
            ...values,
            rating: Number(values.rating),
          },
          {
            onSuccess: () => {
              setStatus({
                isValid: true,
              });
              if (onSuccess) {
                onSuccess(values);
              }
              resetForm();
            },
            onError: (error) => {
              setStatus({
                isValid: false,
                msg: `${"status" in error ? error.status : error.message}`,
              });
              if ("fields" in error) {
                // @ts-expect-error Ignore Eslint error
                setErrors(error.fields);
              }
            },
            onSettled: () => {
              setSubmitting(false);
            },
          }
        );
      }}
    >
      {({
        values,
        errors,
        status,
        handleBlur,
        handleChange,
        handleSubmit,
        isSubmitting,
      }) => (
        <form onSubmit={handleSubmit} data-testid="review-form">
          <FormGroup>
            <p>
              <span className="text-lg font-semibold">{username}</span>
              {/* @ts-expect-error Ignore Eslint error */}
              <Rating value={Number(values?.rating ?? 0)} name="rating" className="ml-2" size="small"
                      onChange={handleChange} onBlur={handleBlur} data-testid="review-rating"
              />
              {/* @ts-expect-error Ignore Eslint error */}
              {errors.rating && <span className="block text-sm text-red-500">{errors.rating}</span>}
            </p>
          </FormGroup>
          <FormGroup>
            <TextareaAutosize
              className="mt-2 mb-2 text-justify text-sm font-normal font-sans leading-5 p-3 rounded rounded-br-none shadow-md shadow-slate-100 focus:shadow-outline-purple focus:shadow-lg border border-solid border-slate-300 hover:border-purple-500 focus:border-purple-500 bg-white text-slate-900 focus-visible:outline-0"
              // @ts-expect-error Ignore Eslint error
              aria-label="Review body" name="body" value={values?.body ?? ""} placeholder="Add a review..."
              onChange={handleChange} onBlur={handleBlur} data-testid="review-body"
            />
            {/* @ts-expect-error Ignore Eslint error */}
            {errors.body && <span className="block text-sm text-red-500">{errors.body}</span>}
          </FormGroup>
          {status && status.msg && (
            <div
              className={`${
                status.isValid
                  ? "text-cyan-700 border-cyan-500 bg-cyan-200/50"
                  : "text-red-700 border-red-400 bg-red-100"
              }`}
              role="alert"
            >
              {status.msg}
            </div>
          )}
          <button className="mt-4 px-10 py-3 font-semibold text-sm bg-cyan-500 text-white rounded shadow-sm"
                  type="submit" disabled={isSubmitting}>
            Submit
          </button>
        </form>
      )}
    </Formik>
  );
};
