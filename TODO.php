## TODO

- [ ] Rename enroll_tokens to enrollment_tokens because in Moodle an "enroll" really means "a method for enrolling" and is not student-specific
- [ ] on activate.php allow to do mulitple tokens at once
  - [ ] plus and minus tokens on javascript
- [ ] on activate.php validate and recommend fixes for email address // see PMT code for gamil.com
- [ ] Add testing for the case where you enroll a student and then they expire and re-enroll again
  - [ ] it must reset progress

## Version 2 (i.e. won't do it, 100% perfect pull requests will be reviewed but not merged for a while)

- more fine-grained permission control, more people can create tokens for some subset of courses

---

# Documentation

- Will reset the enrolment if the user is already enrolled but not active (this feature is not configurable)
- Note: there is some thing called "front page" which everyone is enrolled in by default so it gets weird if you make a token for that