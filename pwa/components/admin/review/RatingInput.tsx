import { Labeled, useInput } from "react-admin";
import { type CommonInputProps, type ResettableTextFieldProps } from "ra-ui-materialui";
import Rating, { type RatingProps } from "@mui/material/Rating";

export type RatingInputProps = RatingProps & CommonInputProps & Omit<ResettableTextFieldProps, 'label' | 'helperText'>;

export const RatingInput = (props: RatingInputProps) => {
  const { field: { ref, ...field} } = useInput(props);
  const value = Number(field.value);
  // Error with "helperText" and "validate" props: remove them from the Rating component
  const { helperText, validate, ...rest } = props;

  return (
    <Labeled>
      <Rating {...rest} {...field} value={value}/>
    </Labeled>
  );
};
RatingInput.displayName = "RatingInput";
RatingInput.defaultProps = { label: "Rating" };
