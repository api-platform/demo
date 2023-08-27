import {Formik} from "formik";
import {type FunctionComponent} from "react";
import {type UseMutationResult} from "react-query";
import {Checkbox, debounce, FormControlLabel, FormGroup, TextField, Typography} from "@mui/material";

import {type FiltersProps} from "@/utils/book";
import {type FetchError, type FetchResponse} from "@/utils/dataAccess";
import {type PagedCollection} from "@/types/collection";
import {type Book} from "@/types/Book";

interface Props {
  filters: FiltersProps | undefined;
  mutation: UseMutationResult<FetchResponse<PagedCollection<Book>>>;
}

export const Filters: FunctionComponent<Props> = ({ filters, mutation }) => (
  <Formik
    initialValues={filters ?? {}}
    enableReinitialize={true}
    onSubmit={(values, { setSubmitting, setStatus, setErrors }) => {
      mutation.mutate(
        values,
        {
          onSuccess: () => {
            setStatus({
              isValid: true,
            });
          },
          // @ts-ignore
          onError: (error: Error | FetchError) => {
            setStatus({
              isValid: false,
              msg: error.message,
            });
            if ("fields" in error) {
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
      handleChange,
      handleSubmit,
      submitForm,
    }) => (
      <form onSubmit={handleSubmit}>
        <FormGroup className="mb-4">
          <FormControlLabel name="author" labelPlacement="top" className="!m-0" label={
            <Typography className="font-semibold w-full">Author</Typography>
          } control={
            <TextField value={values?.author ?? ""} placeholder="Search by author..." type="search"
                       data-testid="filter-author" variant="standard" className="w-full" onChange={(e) => {
                         handleChange(e);
                         debounce(submitForm, 1000)();
                       }}
            />
          }/>
        </FormGroup>
        <FormGroup className="mb-4">
          <FormControlLabel name="title" labelPlacement="top" className="!m-0" label={
            <Typography className="font-semibold w-full">Title</Typography>
          } control={
            <TextField value={values?.title ?? ""} placeholder="Search by title..." type="search"
                       data-testid="filter-title" variant="standard" className="w-full" onChange={(e) => {
                         handleChange(e);
                         debounce(submitForm, 1000)();
                       }}
            />
          }/>
        </FormGroup>
        <FormGroup>
          <ul className="block">
            <p className="font-semibold">Condition</p>
            <li>
              <FormControlLabel name="condition" label="New" control={<Checkbox data-testid="filter-condition-new"/>}
                                checked={!!values?.condition?.includes("https://schema.org/NewCondition")}
                                value="https://schema.org/NewCondition"
                                onChange={(e) => {
                                  handleChange(e);
                                  submitForm();
                                }}
              />
            </li>
            <li>
              <FormControlLabel name="condition" label="Damaged" control={<Checkbox data-testid="filter-condition-damaged"/>}
                                checked={!!values?.condition?.includes("https://schema.org/DamagedCondition")}
                                value="https://schema.org/DamagedCondition"
                                onChange={(e) => {
                                  handleChange(e);
                                  submitForm();
                                }}
              />
            </li>
            <li>
              <FormControlLabel name="condition" label="Refurbished" control={<Checkbox data-testid="filter-condition-refurbished"/>}
                                checked={!!values?.condition?.includes("https://schema.org/RefurbishedCondition")}
                                value="https://schema.org/RefurbishedCondition"
                                onChange={(e) => {
                                  handleChange(e);
                                  submitForm();
                                }}
              />
            </li>
            <li>
              <FormControlLabel name="condition" label="Used" control={<Checkbox data-testid="filter-condition-used"/>}
                                checked={!!values?.condition?.includes("https://schema.org/UsedCondition")}
                                value="https://schema.org/UsedCondition"
                                onChange={(e) => {
                                  handleChange(e);
                                  submitForm();
                                }}
              />
            </li>
          </ul>
        </FormGroup>
      </form>
    )}
  </Formik>
);
